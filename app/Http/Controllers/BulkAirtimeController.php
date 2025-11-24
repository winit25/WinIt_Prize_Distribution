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
use Illuminate\Validation\ValidationException;
use App\Rules\NigerianPhoneNumber;
use App\Rules\ValidAmount;
use App\Jobs\ProcessBatchJob;
use App\Services\CsvEncryptionService;
use App\Services\SharePointService;

class BulkAirtimeController extends Controller
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
     * Display the airtime upload form
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user || !$user->canUploadCsv()) {
            abort(403, 'You do not have permission to access the CSV upload page.');
        }

        $passwordVerified = session('airtime_upload_password_verified', false);
        
        $recentBatches = BatchUpload::where('batch_type', 'airtime')
            ->with('recipients')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('bulk-airtime.index', compact('recentBatches', 'passwordVerified'));
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
            session(['airtime_upload_password_verified' => true]);
            
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
     * Handle CSV file upload for airtime
     */
    public function upload(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->canUploadCsv()) {
            abort(403, 'You do not have permission to upload CSV files.');
        }

        DB::beginTransaction();

        try {
            // Handle file upload or SharePoint URL
            $file = $request->file('csv_file');
            $sharepointUrl = $request->input('sharepoint_url');
            $isSharePoint = !empty($sharepointUrl);
            
            if (!$file && !$isSharePoint) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please upload a CSV file or provide a SharePoint URL.'
                ], 400);
            }

            $fullPath = null;
            $originalName = null;
            $decryptedPath = null;

            if ($isSharePoint) {
                // Download from SharePoint
                try {
                    $downloadedFile = $this->sharePointService->downloadFile($sharepointUrl);
                    $fullPath = $downloadedFile['path'];
                    $originalName = $downloadedFile['filename'];
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to download file from SharePoint: ' . $e->getMessage()
                    ], 400);
                }
            } else {
                // Handle regular file upload
                $request->validate([
                    'csv_file' => 'required|file|mimes:csv,txt|max:2048'
                ]);

                $file = $request->file('csv_file');
                $originalName = $file->getClientOriginalName();
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $filePath = $file->storeAs('csv_uploads', $filename, 'local');
                $fullPath = storage_path('app/' . $filePath);

                // Check if file is encrypted
                if ($this->csvEncryptionService->isEncrypted($fullPath)) {
                    $csvPassword = $request->input('csv_password');
                    if (!$csvPassword) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'This CSV file is encrypted. Please provide the password.',
                            'requires_password' => true
                        ], 400);
                    }

                    $validation = $this->csvEncryptionService->validateEncryptedFile($fullPath, $csvPassword);
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
            $storedFilename = $isSharePoint ? $originalName : $filename;
            
            $batchUpload = BatchUpload::create([
                'filename' => $storedFilename,
                'batch_name' => $request->batch_name ?? 'Airtime Batch ' . date('Y-m-d H:i:s'),
                'batch_type' => 'airtime',
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
                    'amount' => $recipientData['amount'],
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            // Log activity
            try {
                app(\App\Services\ActivityLoggingService::class)->logBatchCreated($batchUpload);
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
            Log::error('Airtime batch upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse CSV file for airtime (simpler format: name, phone_number, amount)
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
            
            // Expected columns: name, phone_number, amount
            // Optional: customer_name, email
            $expectedColumns = ['name', 'phone_number', 'amount'];
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
                $email = trim($rowData['email'] ?? '') ?: null;
                $customerName = trim($rowData['customer_name'] ?? '') ?: null;
                $amount = floatval($rowData['amount'] ?? 0);
                
                // Validate email if provided
                if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
                
                // Validate amount for airtime (₦50 - ₦10,000)
                if ($amount < 50 || $amount > 10000) {
                    $skippedRows++;
                    continue;
                }
                
                if (empty($name) || empty($phoneNumber) || $amount <= 0) {
                    $skippedRows++;
                    continue;
                }

                $recipients[] = [
                    'name' => $name,
                    'customer_name' => $customerName,
                    'phone_number' => $phoneNumber,
                    'email' => $email,
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
        if ($batch->batch_type !== 'airtime') {
            return response()->json([
                'success' => false,
                'message' => 'This batch is not an airtime batch.'
            ], 400);
        }

        if ($batch->status !== 'uploaded') {
            return response()->json([
                'success' => false,
                'message' => 'Batch has already been processed or is currently processing.'
            ], 400);
        }

        try {
            $job = new ProcessBatchJob($batch);
            $job->handle();

            $batch->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Batch processing completed.',
                'batch' => $batch
            ]);
        } catch (\Exception $e) {
            Log::error('Batch processing failed', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Batch processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get batch status
     */
    public function getBatchStatus(BatchUpload $batch)
    {
        return response()->json([
            'success' => true,
            'batch' => $batch->load(['recipients', 'transactions'])
        ]);
    }

    /**
     * Download sample CSV
     */
    public function downloadSample()
    {
        $sampleContent = "name,phone_number,amount\n";
        $sampleContent .= "John Doe,08012345678,500\n";
        $sampleContent .= "Jane Smith,08123456789,1000\n";
        $sampleContent .= "Mike Johnson,07012345678,2000\n";

        return response($sampleContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="sample_airtime_recipients.csv"');
    }

    /**
     * View batch history
     */
    public function history(Request $request)
    {
        // Get filter parameters
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $searchQuery = $request->get('search', '');

        // Build query - only airtime batches
        $query = BatchUpload::where('batch_type', 'airtime')
            ->with(['recipients', 'transactions']);

        // Search query filter - sanitize user input
        if (!empty($searchQuery)) {
            $sanitizedQuery = $this->sanitizeSearchInput($searchQuery);
            $query->where(function($q) use ($sanitizedQuery) {
                $q->where('batch_name', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('filename', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('notes', 'like', "%{$sanitizedQuery}%");
            });
        }

        // Date filter
        if (!empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Status filter - validate enum values
        $status = $request->get('status');
        if (!empty($status)) {
            $validStatuses = ['uploaded', 'processing', 'completed', 'failed'];
            if (in_array($status, $validStatuses)) {
                $query->where('status', $status);
            }
        }

        $batches = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());

        return view('bulk-airtime.history', compact('batches'));
    }

    /**
     * View batch details
     */
    public function show($batchId)
    {
        // Validate batch ID to prevent SQL injection
        $validator = Validator::make(['batch_id' => $batchId], [
            'batch_id' => 'required|integer|exists:batch_uploads,id'
        ]);

        if ($validator->fails()) {
            abort(404, 'Batch not found');
        }

        $batch = BatchUpload::where('batch_type', 'airtime')
            ->with(['recipients', 'transactions'])
            ->findOrFail($batchId);

        return view('bulk-airtime.show', compact('batch'));
    }

    /**
     * View transaction history
     */
    public function transactions(Request $request)
    {
        // Get filter parameters
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $recipientName = $request->get('recipient_name');
        $searchQuery = $request->get('search', '');
        $statusFilter = $request->get('status');

        // Build query - only airtime transactions
        $query = Transaction::with(['recipient', 'batchUpload'])
            ->whereHas('batchUpload', function($q) {
                $q->where('batch_type', 'airtime');
            });

        // Search query filter - sanitize user input
        if (!empty($searchQuery)) {
            $sanitizedQuery = $this->sanitizeSearchInput($searchQuery);
            $query->where(function($q) use ($sanitizedQuery) {
                $q->where('phone_number', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('buypower_reference', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('order_id', 'like', "%{$sanitizedQuery}%")
                  ->orWhereHas('recipient', function($subQ) use ($sanitizedQuery) {
                      $subQ->where('name', 'like', "%{$sanitizedQuery}%")
                           ->orWhere('customer_name', 'like', "%{$sanitizedQuery}%")
                           ->orWhere('phone_number', 'like', "%{$sanitizedQuery}%");
                  });
            });
        }

        // Date filter
        if (!empty($dateFrom)) {
            $query->whereDate('processed_at', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $query->whereDate('processed_at', '<=', $dateTo);
        }

        // Recipient name filter - sanitize
        if (!empty($recipientName)) {
            $sanitizedName = $this->sanitizeSearchInput($recipientName);
            $query->whereHas('recipient', function($q) use ($sanitizedName) {
                $q->where(function($subQ) use ($sanitizedName) {
                    $subQ->where('name', 'like', "%{$sanitizedName}%")
                         ->orWhere('customer_name', 'like', "%{$sanitizedName}%");
                });
            });
        }

        // Status filter - validate enum values
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

        // Calculate stats (filtered to airtime only)
        $statsQuery = Transaction::whereHas('batchUpload', function($q) {
            $q->where('batch_type', 'airtime');
        });
        
        if (!empty($statusFilter)) {
            $statsQuery->where('status', $statusFilter);
        }
        
        $stats = [
            'successful' => Transaction::whereHas('batchUpload', function($q) {
                $q->where('batch_type', 'airtime');
            })->where('status', 'success')->count(),
            'failed' => Transaction::whereHas('batchUpload', function($q) {
                $q->where('batch_type', 'airtime');
            })->where('status', 'failed')->count(),
            'total_amount' => Transaction::whereHas('batchUpload', function($q) {
                $q->where('batch_type', 'airtime');
            })->where('status', 'success')->sum('amount'),
        ];
        $total = $stats['successful'] + $stats['failed'];
        $stats['success_rate'] = $total > 0 ? ($stats['successful'] / $total) * 100 : 0;

        return view('bulk-airtime.transactions', compact('transactions', 'stats'));
    }

    /**
     * Sanitize search input to prevent SQL injection
     */
    protected function sanitizeSearchInput(string $input): string
    {
        // Remove SQL wildcards and dangerous characters
        $input = trim($input);
        $input = str_replace(['%', '_', ';', '--', '/*', '*/'], '', $input);
        // Limit length
        return substr($input, 0, 100);
    }
}
