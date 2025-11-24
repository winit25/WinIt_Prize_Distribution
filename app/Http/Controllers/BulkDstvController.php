<?php

namespace App\Http\Controllers;

use App\Models\BatchUpload;
use App\Models\Recipient;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Rules\NigerianPhoneNumber;
use App\Rules\ValidAmount;
use App\Jobs\ProcessBatchJob;
use App\Services\CsvEncryptionService;
use App\Services\SharePointService;

class BulkDstvController extends Controller
{
    protected $buyPowerService;
    protected $csvEncryptionService;
    protected $sharePointService;

    public function __construct()
    {
        $this->buyPowerService = app('buypower.api');
        $this->csvEncryptionService = new CsvEncryptionService();
        $this->sharePointService = new SharePointService();
    }

    /**
     * Display the DSTV upload form
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user || !$user->canUploadCsv()) {
            abort(403, 'You do not have permission to access the CSV upload page.');
        }

        $passwordVerified = session('dstv_upload_password_verified', false);
        
        $recentBatches = BatchUpload::where('batch_type', 'dstv')
            ->with('recipients')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('bulk-dstv.index', compact('recentBatches', 'passwordVerified'));
    }

    /**
     * Verify upload password
     */
    public function verifyPassword(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to access this page.'
            ], 401);
        }

        if (!$user->canUploadCsv()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to upload CSV files.'
            ], 403);
        }
        
        $request->validate([
            'password' => 'required|string'
        ]);

        $password = $request->input('password');

        if (Hash::check($password, $user->password)) {
            session(['dstv_upload_password_verified' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Password verified successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid password. Please enter your account password.'
        ], 401);
    }

    /**
     * Handle CSV file upload for DSTV
     */
    public function upload(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->canUploadCsv()) {
            abort(403, 'You do not have permission to upload CSV files.');
        }

        // Validate file size
        if ($request->hasFile('csv_file') && $request->file('csv_file')->getSize() > 5242880) {
            return response()->json([
                'success' => false,
                'message' => 'File size exceeds 5MB limit'
            ], 413);
        }

        $validator = Validator::make($request->all(), [
            'csv_file' => 'required_without:sharepoint_url|file|mimes:csv,txt|mimetypes:text/csv,text/plain,application/csv|max:5120',
            'sharepoint_url' => 'required_without:csv_file|url|max:2048',
            'batch_name' => 'nullable|string|max:255',
            'csv_password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $fullPath = null;
            $originalName = null;
            $isSharePoint = false;
            $decryptedPath = null;

            // Handle SharePoint URL upload
            if ($request->filled('sharepoint_url')) {
                $sharepointUrl = $request->input('sharepoint_url');
                
                Log::info('Downloading DSTV file from SharePoint', ['url' => $sharepointUrl]);

                $downloadResult = $this->sharePointService->downloadFromSharingLink($sharepointUrl);
                
                if (!$downloadResult['success']) {
                    $downloadResult = $this->sharePointService->downloadFile($sharepointUrl);
                }

                if (!$downloadResult['success']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to download file from SharePoint: ' . ($downloadResult['error'] ?? 'Unknown error'),
                    ], 400);
                }

                $fullPath = $downloadResult['file_path'];
                $originalName = $downloadResult['filename'];
                $isSharePoint = true;

            } else {
                // Handle regular file upload
                $file = $request->file('csv_file');
                if (!$file->isValid()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Uploaded file is not valid.'
                    ], 400);
                }

                $originalName = $file->getClientOriginalName();
                $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $filename = time() . '_' . $sanitizedName;
                
                $filePath = Storage::disk('local')->putFileAs('uploads', $file, $filename);
                $fullPath = Storage::disk('local')->path($filePath);
            }

            // Check if file is encrypted
            $csvContent = file_get_contents($fullPath);
            $isEncrypted = $this->csvEncryptionService->isEncrypted($originalName);
            
            if ($isEncrypted) {
                $csvPassword = $request->input('csv_password');
                if (!$csvPassword) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'CSV file is encrypted. Please provide the password to decrypt it.',
                        'requires_password' => true
                    ], 400);
                }

                $validation = $this->csvEncryptionService->validateEncryptedFile($filePath ?? $fullPath, $csvPassword);
                if (!$validation['valid']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid password or corrupted encrypted file.',
                        'requires_password' => true
                    ], 400);
                }

                $decryptedPath = storage_path('app/temp/' . uniqid('decrypted_') . '.csv');
                if (!is_dir(dirname($decryptedPath))) {
                    mkdir(dirname($decryptedPath), 0755, true);
                }
                file_put_contents($decryptedPath, $validation['content']);
                $fullPath = $decryptedPath;
            }

            // Parse and validate CSV file
            $parseResult = $this->parseCsvFile($fullPath);
            $recipients = $parseResult['recipients'];
            $skippedRows = $parseResult['skipped_rows'];

            if (empty($recipients)) {
                DB::rollBack();
                if ($decryptedPath && file_exists($decryptedPath)) {
                    unlink($decryptedPath);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'CSV validation failed: No valid recipients found.',
                    'skipped_rows' => $skippedRows,
                ], 422);
            }

            // Check for duplicate phone numbers
            $phoneNumbers = array_column($recipients, 'phone_number');
            $duplicatePhones = array_diff_assoc($phoneNumbers, array_unique($phoneNumbers));
            if (!empty($duplicatePhones)) {
                DB::rollBack();
                if ($decryptedPath && file_exists($decryptedPath)) {
                    unlink($decryptedPath);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate phone numbers found in the same batch.',
                    'duplicate_phones' => array_unique($duplicatePhones),
                ], 422);
            }

            // Clean up temp file
            if ($decryptedPath && file_exists($decryptedPath)) {
                unlink($decryptedPath);
            }

            // Create batch upload record
            $storedFilename = $isSharePoint ? $originalName : ($filename ?? $originalName);
            
            $batchUpload = BatchUpload::create([
                'filename' => $storedFilename,
                'batch_name' => $request->batch_name ?? 'DSTV Batch ' . date('Y-m-d H:i:s'),
                'batch_type' => 'dstv',
                'total_recipients' => count($recipients),
                'total_amount' => array_sum(array_column($recipients, 'amount')),
                'status' => 'uploaded',
                'sms_template' => $request->input('sms_template'),
                'email_template' => $request->input('email_template'),
                'enable_sms' => $request->has('enable_sms') ? filter_var($request->input('enable_sms'), FILTER_VALIDATE_BOOLEAN) : true,
                'enable_email' => $request->has('enable_email') ? filter_var($request->input('enable_email'), FILTER_VALIDATE_BOOLEAN) : true,
                'user_id' => auth()->id()
            ]);

            // Create recipient records
            foreach ($recipients as $recipientData) {
                Recipient::create([
                    'batch_upload_id' => $batchUpload->id,
                    'name' => $recipientData['name'],
                    'customer_name' => $recipientData['customer_name'] ?? null,
                    'phone_number' => $recipientData['phone_number'],
                    'email' => $recipientData['email'] ?? null,
                    'meter_number' => $recipientData['meter_number'], // DSTV smartcard number
                    'disco' => $recipientData['disco'] ?? 'DSTV', // Should be "DSTV"
                    'meter_type' => 'prepaid', // Fixed for DSTV
                    'amount' => $recipientData['amount'],
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            // Log activity
            try {
                app(\App\Services\ActivityLoggingService::class)->logBatchCreated($batchUpload, $user);
            } catch (\Exception $e) {
                Log::warning('Failed to log batch creation', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'CSV file uploaded successfully. Batch created with ' . count($recipients) . ' recipients.',
                'batch_id' => $batchUpload->id,
                'batch_name' => $batchUpload->batch_name,
                'total_recipients' => count($recipients),
                'skipped_rows' => $skippedRows,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($decryptedPath) && file_exists($decryptedPath)) {
                @unlink($decryptedPath);
            }
            
            Log::error('DSTV batch upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed. Please check your file and try again.'
            ], 500);
        }
    }

    /**
     * Parse CSV file for DSTV (format: name, phone_number, meter (smartcard), amount, email (optional), customer_name (optional))
     */
    protected function parseCsvFile(string $filePath): array
    {
        $recipients = [];
        
        if (!file_exists($filePath)) {
            throw new \Exception("CSV file not found at path: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Unable to read CSV file');
        }

        try {
            // Read header row
            $header = fgetcsv($handle, 0, ',', '"', '\\');
            if (!$header || empty($header)) {
                throw new \Exception('CSV file is empty or has no header row');
            }

            // Clean and normalize header
            $header = array_map(function($col) {
                return strtolower(trim($col, " \t\n\r\0\x0B\xEF\xBB\xBF"));
            }, $header);
            
            // Expected columns: name, phone_number, meter (smartcard number for DSTV), disco, amount
            // Optional: customer_name, email
            $expectedColumns = ['name', 'phone_number', 'meter', 'disco', 'amount'];
            $missingColumns = [];
            
            foreach ($expectedColumns as $column) {
                if (!in_array($column, $header)) {
                    $missingColumns[] = $column;
                }
            }
            
            if (!empty($missingColumns)) {
                throw new \Exception("Missing required columns: " . implode(', ', $missingColumns) . ". Found columns: " . implode(', ', $header));
            }

            $lineNumber = 1;
            $skippedRows = 0;
            
            while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                $lineNumber++;
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                if (count($row) !== count($header)) {
                    $skippedRows++;
                    continue;
                }

                $rowData = array_combine($header, $row);
                
                $name = trim($rowData['name'] ?? '');
                $phoneNumber = trim($rowData['phone_number'] ?? '');
                $meterNumber = trim($rowData['meter'] ?? ''); // DSTV smartcard number (10-11 digits)
                $disco = strtoupper(trim($rowData['disco'] ?? '')); // Should be "DSTV"
                $email = trim($rowData['email'] ?? '') ?: null;
                $customerName = trim($rowData['customer_name'] ?? '') ?: null;
                $amount = floatval($rowData['amount'] ?? 0);
                
                // Validate email if provided
                if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $skippedRows++;
                    continue;
                }

                // Validate meter (DSTV smartcard number - 10-11 digits)
                if (empty($meterNumber) || !preg_match('/^\d{10,11}$/', $meterNumber)) {
                    $skippedRows++;
                    continue;
                }

                // Validate disco (must be DSTV)
                if (empty($disco) || $disco !== 'DSTV') {
                    $skippedRows++;
                    continue;
                }

                // Validate using custom rules
                $validator = Validator::make([
                    'phone_number' => $phoneNumber,
                    'amount' => $amount
                ], [
                    'phone_number' => [new NigerianPhoneNumber()],
                    'amount' => [new ValidAmount()]
                ]);

                if ($validator->fails()) {
                    $skippedRows++;
                    continue;
                }
                
                // Validate amount (minimum ₦100, maximum ₦100,000 for DSTV)
                if ($amount < 100 || $amount > 100000) {
                    $skippedRows++;
                    continue;
                }
                
                if (empty($name) || empty($phoneNumber) || empty($meterNumber) || empty($disco) || $amount <= 0) {
                    $skippedRows++;
                    continue;
                }

                $recipients[] = [
                    'name' => $name,
                    'customer_name' => $customerName,
                    'phone_number' => $phoneNumber,
                    'email' => $email,
                    'meter_number' => $meterNumber, // DSTV smartcard number
                    'disco' => $disco, // Should be "DSTV"
                    'amount' => $amount
                ];
            }
            
            if (empty($recipients)) {
                throw new \Exception("No valid recipients found in CSV file. Total lines processed: " . ($lineNumber - 1) . ", Skipped: {$skippedRows}");
            }

        } finally {
            fclose($handle);
        }
        
        return [
            'recipients' => $recipients,
            'skipped_rows' => $skippedRows
        ];
    }

    /**
     * Process batch
     */
    public function processBatch(BatchUpload $batch)
    {
        if ($batch->batch_type !== 'dstv') {
            return response()->json([
                'success' => false,
                'message' => 'This batch is not a DSTV batch.'
            ], 422);
        }

        if (!in_array($batch->status, ['uploaded', 'failed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Batch has already been processed or is currently processing.',
                'current_status' => $batch->status
            ], 422);
        }

        $batch->load('recipients');

        if ($batch->recipients->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Batch has no recipients to process'
            ], 422);
        }

        $batch->update(['status' => 'processing']);

        Log::info('Starting DSTV batch processing', [
            'batch_id' => $batch->id,
            'total_recipients' => $batch->total_recipients
        ]);

        try {
            $job = new ProcessBatchJob($batch);
            $job->handle();

            $batch->refresh();

            return response()->json([
                'success' => true,
                'message' => 'DSTV distribution started! Processing ' . $batch->total_recipients . ' recipients...',
                'batch_id' => $batch->id,
                'total_recipients' => $batch->total_recipients,
                'batch_status' => $batch->status,
            ]);
        } catch (\Exception $e) {
            Log::error('DSTV batch processing failed', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage()
            ]);

            $batch->refresh();
            if ($batch->status === 'processing') {
                $batch->update([
                    'status' => 'failed',
                    'error_message' => 'Processing failed: ' . $e->getMessage()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to start processing. Please try again.'
            ], 500);
        }
    }

    /**
     * Get batch status
     */
    public function getBatchStatus($batchId)
    {
        $validator = Validator::make(['batch_id' => $batchId], [
            'batch_id' => 'required|integer|exists:batch_uploads,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid batch ID'
            ], 422);
        }

        $batch = BatchUpload::where('batch_type', 'dstv')
            ->with(['recipients', 'transactions'])
            ->findOrFail($batchId);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $batch->id,
                'status' => $batch->status,
                'total_recipients' => $batch->total_recipients,
                'processed_recipients' => $batch->processed_recipients,
                'successful_transactions' => $batch->successful_transactions,
                'failed_transactions' => $batch->failed_transactions,
                'total_amount' => $batch->total_amount,
                'completion_percentage' => $batch->completion_percentage,
                'success_rate' => $batch->success_rate
            ]
        ]);
    }

    /**
     * Download sample CSV
     */
    public function downloadSample()
    {
        $sampleContent = "name,phone_number,meter,disco,amount,email,customer_name\n";
        $sampleContent .= "John Doe,08012345678,1056356039,DSTV,17150,john@example.com,John Doe\n";
        $sampleContent .= "Jane Smith,08123456789,1056356040,DSTV,20000,jane@example.com,Jane Smith\n";
        $sampleContent .= "Mike Johnson,07012345678,1056356041,DSTV,15000,,Mike Johnson\n";

        return response($sampleContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="sample_dstv_recipients.csv"');
    }

    /**
     * View batch history
     */
    public function history(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $searchQuery = $request->get('search', '');

        $query = BatchUpload::where('batch_type', 'dstv')
            ->with(['recipients', 'transactions']);

        if (!empty($searchQuery)) {
            $sanitizedQuery = $this->sanitizeSearchInput($searchQuery);
            $query->where(function($q) use ($sanitizedQuery) {
                $q->where('batch_name', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('filename', 'like', "%{$sanitizedQuery}%");
            });
        }

        if (!empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $status = $request->get('status');
        if (!empty($status)) {
            $validStatuses = ['uploaded', 'processing', 'completed', 'failed'];
            if (in_array($status, $validStatuses)) {
                $query->where('status', $status);
            }
        }

        $batches = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());

        return view('bulk-dstv.history', compact('batches'));
    }

    /**
     * View batch details
     */
    public function show($batchId)
    {
        $validator = Validator::make(['batch_id' => $batchId], [
            'batch_id' => 'required|integer|exists:batch_uploads,id'
        ]);

        if ($validator->fails()) {
            abort(404, 'Batch not found');
        }

        $batch = BatchUpload::where('batch_type', 'dstv')
            ->with(['recipients', 'transactions'])
            ->findOrFail($batchId);

        return view('bulk-dstv.show', compact('batch'));
    }

    /**
     * View transaction history
     */
    public function transactions(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $searchQuery = $request->get('search', '');
        $statusFilter = $request->get('status');

        $query = Transaction::with(['recipient', 'batchUpload'])
            ->whereHas('batchUpload', function($q) {
                $q->where('batch_type', 'dstv');
            });

        if (!empty($searchQuery)) {
            $sanitizedQuery = $this->sanitizeSearchInput($searchQuery);
            $query->where(function($q) use ($sanitizedQuery) {
                $q->where('phone_number', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('buypower_reference', 'like', "%{$sanitizedQuery}%")
                  ->orWhereHas('recipient', function($subQ) use ($sanitizedQuery) {
                      $subQ->where('name', 'like', "%{$sanitizedQuery}%")
                           ->orWhere('meter_number', 'like', "%{$sanitizedQuery}%");
                  });
            });
        }

        if (!empty($dateFrom)) {
            $query->whereDate('processed_at', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $query->whereDate('processed_at', '<=', $dateTo);
        }

        if (!empty($statusFilter)) {
            $validStatuses = ['success', 'failed', 'pending', 'processing'];
            if (in_array($statusFilter, $validStatuses)) {
                $query->where('status', $statusFilter);
            }
        }

        $transactions = $query->orderBy('processed_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->appends($request->query());

        $stats = [
            'successful' => Transaction::whereHas('batchUpload', function($q) {
                $q->where('batch_type', 'dstv');
            })->where('status', 'success')->count(),
            'failed' => Transaction::whereHas('batchUpload', function($q) {
                $q->where('batch_type', 'dstv');
            })->where('status', 'failed')->count(),
            'total_amount' => Transaction::whereHas('batchUpload', function($q) {
                $q->where('batch_type', 'dstv');
            })->where('status', 'success')->sum('amount'),
        ];
        $total = $stats['successful'] + $stats['failed'];
        $stats['success_rate'] = $total > 0 ? ($stats['successful'] / $total) * 100 : 0;

        return view('bulk-dstv.transactions', compact('transactions', 'stats'));
    }

    /**
     * Download batch report as CSV
     */
    public function downloadBatchReport($batchId)
    {
        $validator = Validator::make(['batch_id' => $batchId], [
            'batch_id' => 'required|integer|exists:batch_uploads,id'
        ]);

        if ($validator->fails()) {
            abort(404, 'Batch not found');
        }

        $batch = BatchUpload::where('batch_type', 'dstv')
            ->with(['recipients', 'transactions'])
            ->findOrFail($batchId);

        // Create CSV content
        $filename = 'dstv-batch-report-' . $batch->id . '-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($batch) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Recipient Name',
                'Phone Number',
                'Smartcard Number',
                'Amount (₦)',
                'Status',
                'Reference',
                'Processed Date',
                'Error Message'
            ]);

            // Get all recipients with their transactions
            foreach ($batch->recipients as $recipient) {
                $transaction = $batch->transactions->where('recipient_id', $recipient->id)->first();
                
                $row = [
                    $recipient->name ?? '',
                    $recipient->phone_number ?? '',
                    $recipient->meter_number ?? '', // Smartcard number
                    number_format($recipient->amount, 2),
                    strtoupper($recipient->status ?? 'pending'),
                    $transaction && isset($transaction->buypower_reference) ? $transaction->buypower_reference : 'N/A',
                    $transaction && $transaction->processed_at ? $transaction->processed_at->format('Y-m-d H:i:s') : 'N/A',
                    $recipient->error_message ?? ''
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Sanitize search input
     */
    protected function sanitizeSearchInput(string $input): string
    {
        $input = trim($input);
        $input = preg_replace('/[%_]/', '', $input);
        $input = preg_replace('/[^a-zA-Z0-9\s\-@.]/', '', $input);
        return mb_substr($input, 0, 255);
    }
}

