<?php

namespace App\Http\Controllers;

use App\Models\BatchUpload;
use App\Models\Recipient;
use App\Models\Transaction;
use App\Services\BuyPowerApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BulkTokenController extends Controller
{
    protected BuyPowerApiService $buyPowerService;

    public function __construct(BuyPowerApiService $buyPowerService)
    {
        $this->buyPowerService = $buyPowerService;
    }

    /**
     * Display the upload form
     */
    public function index()
    {
        $recentBatches = BatchUpload::with('recipients')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('bulk-token.index', compact('recentBatches'));
    }

    /**
     * Handle CSV file upload
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|mimetypes:text/csv,text/plain,application/csv|max:5120', // 5MB max
            'batch_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::error('CSV Upload Validation Failed', [
                'errors' => $validator->errors()->toArray(),
                'file_info' => $request->hasFile('csv_file') ? [
                    'name' => $request->file('csv_file')->getClientOriginalName(),
                    'size' => $request->file('csv_file')->getSize(),
                    'mime' => $request->file('csv_file')->getMimeType(),
                    'error' => $request->file('csv_file')->getError()
                ] : 'No file uploaded'
            ]);
            
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Additional file validation
        if (!$request->hasFile('csv_file')) {
            return response()->json([
                'success' => false,
                'message' => 'No CSV file was uploaded'
            ], 400);
        }
        
        $file = $request->file('csv_file');
        if (!$file->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Uploaded file is not valid. Error code: ' . $file->getError()
            ], 400);
        }

        try {
            DB::beginTransaction();

            // File is already validated above
            $filename = time() . '_' . $file->getClientOriginalName();
            
            Log::info('Starting file upload', [
                'original_name' => $file->getClientOriginalName(),
                'temp_path' => $file->getPathname(),
                'temp_exists' => file_exists($file->getPathname()),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'error' => $file->getError(),
                'is_valid' => $file->isValid()
            ]);
            
            // Store file in uploads directory
            $filePath = $file->storeAs('uploads', $filename);
            
            if (!$filePath) {
                throw new \Exception('Failed to store file. File path is null.');
            }
            
            $fullPath = storage_path('app' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $filePath));
            
            Log::info('File upload attempt', [
                'stored_path' => $filePath,
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath),
                'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0
            ]);
            
            if (!file_exists($fullPath)) {
                throw new \Exception("File was not stored properly. Expected at: {$fullPath}");
            }

            // Parse CSV file
            $recipients = $this->parseCsvFile($fullPath);

            if (empty($recipients)) {
                throw new \Exception('No valid recipients found in CSV file');
            }

            // Create batch upload record
            $batchUpload = BatchUpload::create([
                'filename' => $filename,
                'batch_name' => $request->batch_name ?? 'Batch ' . date('Y-m-d H:i:s'),
                'total_recipients' => count($recipients),
                'total_amount' => array_sum(array_column($recipients, 'amount')),
                'status' => 'uploaded'
            ]);

            // Create recipient records
            foreach ($recipients as $recipientData) {
                Recipient::create([
                    'batch_upload_id' => $batchUpload->id,
                    'name' => $recipientData['name'],
                    'customer_name' => $recipientData['customer_name'],
                    'address' => $recipientData['address'],
                    'phone_number' => $recipientData['phone_number'],
                    'disco' => $recipientData['disco'],
                    'meter_number' => $recipientData['meter_number'],
                    'meter_type' => $recipientData['meter_type'],
                    'amount' => $recipientData['amount'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'CSV file uploaded successfully',
                'batch_id' => $batchUpload->id,
                'total_recipients' => count($recipients),
                'total_amount' => array_sum(array_column($recipients, 'amount'))
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('CSV Upload Error', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName() ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process batch tokens
     */
    public function processBatch(Request $request, $batchId)
    {
        $validator = Validator::make(['batch_id' => $batchId], [
            'batch_id' => 'required|exists:batch_uploads,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid batch ID'
            ], 422);
        }

        try {
            $batch = BatchUpload::with('recipients')
                ->where('id', $batchId)
                ->where('status', 'uploaded')
                ->first();

            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch not found or already processed'
                ], 404);
            }

            // Update batch status to processing
            $batch->update(['status' => 'processing']);

            // Dispatch job to process in background
            \Illuminate\Support\Facades\Artisan::call('buypower:process-batch', [
                'batch_id' => $batchId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Batch processing started',
                'batch_id' => $batchId
            ]);

        } catch (\Exception $e) {
            Log::error('Batch Processing Error', [
                'batch_id' => $batchId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start processing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get batch status
     */
    public function getBatchStatus($batchId)
    {
        $batch = BatchUpload::with(['recipients' => function($query) {
            $query->selectRaw('batch_upload_id, status, count(*) as count')
                  ->groupBy('batch_upload_id', 'status');
        }])->find($batchId);

        if (!$batch) {
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ], 404);
        }

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
     * View batch history
     */
    public function history()
    {
        $batches = BatchUpload::with('recipients')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('bulk-token.history', compact('batches'));
    }

    /**
     * View batch details
     */
    public function show($batchId)
    {
        $batch = BatchUpload::with(['recipients', 'transactions'])
            ->findOrFail($batchId);

        return view('bulk-token.show', compact('batch'));
    }

    /**
     * View transaction history
     */
    public function transactions()
    {
        $transactions = Transaction::with(['recipient'])
            ->orderBy('processed_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Calculate stats
        $stats = [
            'successful' => Transaction::where('status', 'success')->count(),
            'failed' => Transaction::where('status', 'failed')->count(),
            'total_amount' => Transaction::where('status', 'success')->sum('amount'),
        ];
        $total = $stats['successful'] + $stats['failed'];
        $stats['success_rate'] = $total > 0 ? ($stats['successful'] / $total) * 100 : 0;

        return view('bulk-token.transactions', compact('transactions', 'stats'));
    }

    /**
     * Download token details
     */
    public function downloadToken($transactionId)
    {
        $transaction = Transaction::with('recipient')->findOrFail($transactionId);
        
        if (!$transaction->token) {
            return response()->json(['error' => 'No token available for this transaction'], 404);
        }

        $content = "ELECTRICITY TOKEN RECEIPT\n";
        $content .= "========================\n\n";
        $content .= "Recipient: {$transaction->recipient->name}\n";
        $content .= "Phone: {$transaction->phone_number}\n";
        $content .= "Disco: {$transaction->recipient->disco}\n";
        $content .= "Amount: ₦" . number_format($transaction->amount, 2) . "\n";
        $content .= "Units: {$transaction->units} KWh\n";
        $content .= "Token: {$transaction->token}\n";
        $content .= "Reference: {$transaction->buypower_reference}\n";
        $content .= "Date: {$transaction->processed_at->format('M d, Y h:i A')}\n";
        $content .= "\n========================\n";
        $content .= "Generated by BuyPower Bulk Token System\n";

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', "attachment; filename=token-{$transactionId}.txt");
    }

    /**
     * Parse CSV file and return recipients array
     */
    protected function parseCsvFile(string $filePath): array
    {
        $recipients = [];
        
        Log::info('Parsing CSV file', ['path' => $filePath, 'exists' => file_exists($filePath)]);
        
        if (!file_exists($filePath)) {
            throw new \Exception("CSV file not found at path: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new \Exception('CSV file is not readable');
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Unable to read CSV file: ' . error_get_last()['message'] ?? 'Unknown error');
        }

        try {
            // Read header row
            $header = fgetcsv($handle);
            if (!$header || empty($header)) {
                throw new \Exception('CSV file is empty or has no header row');
            }

            // Clean and normalize header
            $header = array_map(function($col) {
                return strtolower(trim($col, " \t\n\r\0\x0B\xEF\xBB\xBF"));
            }, $header);
            
            Log::info('CSV Header found', ['header' => $header]);

            // Expected columns: name, address, phone_number, disco, meter_number, meter_type, amount
            $expectedColumns = ['name', 'address', 'phone_number', 'disco', 'meter_number', 'meter_type', 'amount'];
            
            // Optional columns
            $optionalColumns = ['customer_name'];
            $missingColumns = [];
            
            foreach ($expectedColumns as $column) {
                if (!in_array($column, $header)) {
                    $missingColumns[] = $column;
                }
            }
            
            if (!empty($missingColumns)) {
                throw new \Exception("Missing required columns: " . implode(', ', $missingColumns) . ". Found columns: " . implode(', ', $header) . ". Expected: name, address, phone_number, amount");
            }

            $lineNumber = 1;
            $validRows = 0;
            $skippedRows = 0;
            
            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                if (count($row) !== count($header)) {
                    Log::warning("Row {$lineNumber} has different number of columns than header", [
                        'header_count' => count($header),
                        'row_count' => count($row),
                        'row' => $row
                    ]);
                    $skippedRows++;
                    continue;
                }

                $rowData = array_combine($header, $row);
                
                // Validate row data
                $name = trim($rowData['name'] ?? '');
                $address = trim($rowData['address'] ?? '');
                $phoneNumber = trim($rowData['phone_number'] ?? '');
                $disco = strtoupper(trim($rowData['disco'] ?? ''));
                $meterNumber = trim($rowData['meter_number'] ?? '');
                $meterType = strtolower(trim($rowData['meter_type'] ?? 'prepaid'));
                $customerName = trim($rowData['customer_name'] ?? '') ?: null;
                $amount = floatval($rowData['amount'] ?? 0);

                if (empty($name) || empty($phoneNumber) || empty($disco) || empty($meterNumber) || $amount <= 0) {
                    Log::warning("Skipping invalid row {$lineNumber}", [
                        'name' => $name,
                        'phone' => $phoneNumber,
                        'amount' => $amount,
                        'row' => $rowData
                    ]);
                    $skippedRows++;
                    continue;
                }

                // More flexible phone number validation for Nigerian numbers
                $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
                if (!preg_match('/^(234[789][01]\d{8}|0[789][01]\d{8}|[789][01]\d{8})$/', $cleanPhone)) {
                    Log::warning("Invalid phone number format at line {$lineNumber}: {$phoneNumber} (cleaned: {$cleanPhone})");
                    $skippedRows++;
                    continue;
                }

                // Validate disco code
                $validDiscos = ['AEDC', 'BEDC', 'EKEDC', 'EEDC', 'IBEDC', 'IKEDC', 'JEDC', 'KAEDCO', 'KEDCO', 'PHED', 'YEDC'];
                if (!in_array($disco, $validDiscos)) {
                    Log::warning("Invalid disco code at line {$lineNumber}: {$disco}. Valid codes: " . implode(', ', $validDiscos));
                    $skippedRows++;
                    continue;
                }
                
                // Validate meter type
                $validMeterTypes = ['prepaid', 'postpaid'];
                if (!in_array($meterType, $validMeterTypes)) {
                    Log::warning("Invalid meter type at line {$lineNumber}: {$meterType}. Valid types: prepaid, postpaid");
                    $skippedRows++;
                    continue;
                }
                
                // Validate meter number format (basic validation)
                if (!preg_match('/^[0-9]{10,15}$/', $meterNumber)) {
                    Log::warning("Invalid meter number format at line {$lineNumber}: {$meterNumber}. Should be 10-15 digits");
                    $skippedRows++;
                    continue;
                }

                $recipients[] = [
                    'name' => $name,
                    'customer_name' => $customerName,
                    'address' => $address,
                    'phone_number' => $phoneNumber,
                    'disco' => $disco,
                    'meter_number' => $meterNumber,
                    'meter_type' => $meterType,
                    'amount' => $amount
                ];
                $validRows++;
            }
            
            Log::info('CSV parsing completed', [
                'total_lines' => $lineNumber - 1,
                'valid_rows' => $validRows,
                'skipped_rows' => $skippedRows
            ]);
            
            if (empty($recipients)) {
                throw new \Exception("No valid recipients found in CSV file. Total lines processed: " . ($lineNumber - 1) . ", Skipped: {$skippedRows}");
            }

        } finally {
            fclose($handle);
        }
        
        return $recipients;
    }
}
