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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Rules\NigerianPhoneNumber;
use App\Rules\MeterNumber;
use App\Rules\ValidDiscoCode;
use App\Rules\ValidAmount;
use App\Jobs\ProcessBatchJob;
use App\Services\CsvEncryptionService;
use App\Services\MeterValidationService;
use App\Services\SharePointService;

class BulkTokenController extends Controller
{
    protected $buyPowerService;
    protected $csvEncryptionService;
    protected $meterValidationService;
    protected $sharePointService;

    public function __construct()
    {
        $this->buyPowerService = app('buypower.api');
        $this->csvEncryptionService = new CsvEncryptionService();
        $this->meterValidationService = new MeterValidationService();
        $this->sharePointService = new SharePointService();
    }

    /**
     * Display the upload form
     */
    public function index()
    {
        // Authorization: only users with upload permission can view this page
        $user = auth()->user();
        if (!$user || !$user->canUploadCsv()) {
            abort(403, 'You do not have permission to access the CSV upload page.');
        }

        // Check if user has verified the upload password
        $passwordVerified = session('csv_upload_password_verified', false);
        
        $recentBatches = BatchUpload::with('recipients')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('bulk-token.index', compact('recentBatches', 'passwordVerified'));
    }

    /**
     * Verify upload password - verifies against the authenticated user's password
     */
    public function verifyPassword(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            Log::warning('Password verification attempted without authentication', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to access this page.'
            ], 401);
        }

        // Check if user has permission to upload CSV
        if (!$user->canUploadCsv()) {
            Log::warning('Password verification attempted without permission', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to upload CSV files.'
            ], 403);
        }
        
        // Log the request for debugging
        Log::info('Password verification request received', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => $request->ip(),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'has_csrf_token' => $request->hasHeader('X-CSRF-TOKEN')
        ]);

        // Handle both JSON and form data
        $password = $request->input('password') ?? $request->get('password') ?? null;
        
        if (!$password) {
            // Try to get from JSON body
            $jsonData = json_decode($request->getContent(), true);
            $password = $jsonData['password'] ?? null;
        }
        
        if (!$password) {
            return response()->json([
                'success' => false,
                'message' => 'Password is required.'
            ], 400);
        }

        // Verify password against the authenticated user's password
        if (\Hash::check($password, $user->password)) {
            session(['csv_upload_password_verified' => true]);
            
            Log::info('CSV upload password verified', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Password verified successfully'
            ]);
        }

        Log::warning('CSV upload password verification failed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => $request->ip()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Invalid password. Please enter your account password.'
        ], 401);
    }

    /**
     * Handle CSV file upload
     */
    public function upload(Request $request)
    {
        // Authorization: block users without explicit permission (e.g., Audit role)
        $user = $request->user();
        if (!$user || !$user->canUploadCsv()) {
            try {
                app(\App\Services\ActivityLoggingService::class)->log(
                    'unauthorized_upload_attempt',
                    'Unauthorized attempt to upload CSV',
                    null,
                    [
                        'user_id' => $user ? $user->id : null,
                        'user_email' => $user ? $user->email : null,
                        'ip' => $request->ip(),
                        'path' => $request->path(),
                    ]
                );
            } catch (\Throwable $e) {
                // Swallow logging errors to not mask authorization response
            }
            abort(403, 'You do not have permission to upload CSV files.');
        }

        // Validate file size in bytes before processing
        if ($request->hasFile('csv_file') && $request->file('csv_file')->getSize() > 5242880) {
            return response()->json([
                'success' => false,
                'message' => 'File size exceeds 5MB limit'
            ], 413);
        }
        
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required_without:sharepoint_url|file|mimes:csv,txt|mimetypes:text/csv,text/plain,application/csv|max:5120', // 5MB max
            'sharepoint_url' => 'required_without:csv_file|url|max:2048', // SharePoint file URL or sharing link
            'batch_name' => 'nullable|string|max:255',
            'csv_password' => 'nullable|string|min:6', // Password for encrypted CSV
            'validate_meters' => 'nullable|boolean', // Option to validate meters before upload
        ]);

        if ($validator->fails()) {
            Log::error('CSV Upload Validation Failed', [
                'errors' => $validator->errors()->toArray(),
                'file_info' => $request->hasFile('csv_file') ? [
                    'name' => $request->file('csv_file')->getClientOriginalName(),
                    'size' => $request->file('csv_file')->getSize(),
                    'mime' => $request->file('csv_file')->getMimeType(),
                    'error' => $request->file('csv_file')->getError()
                ] : 'No file uploaded',
                'sharepoint_url' => $request->input('sharepoint_url')
            ]);
            
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $fullPath = null;
            $originalName = null;
            $isSharePoint = false;

            // Handle SharePoint URL upload
            if ($request->filled('sharepoint_url')) {
                $sharepointUrl = $request->input('sharepoint_url');
                
                Log::info('Downloading file from SharePoint', [
                    'url' => $sharepointUrl
                ]);

                $downloadResult = $this->sharePointService->downloadFromSharingLink($sharepointUrl);
                
                if (!$downloadResult['success']) {
                    // Try direct file download if sharing link fails
                    $downloadResult = $this->sharePointService->downloadFile($sharepointUrl);
                }

                if (!$downloadResult['success']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to download file from SharePoint: ' . ($downloadResult['error'] ?? 'Unknown error'),
                        'error' => $downloadResult['error'] ?? 'Unknown error'
                    ], 400);
                }

                $fullPath = $downloadResult['file_path'];
                $originalName = $downloadResult['filename'];
                $isSharePoint = true;

                Log::info('File downloaded from SharePoint', [
                    'filename' => $originalName,
                    'path' => $fullPath,
                    'size' => $downloadResult['size']
                ]);

            } else {
                // Handle regular file upload
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

                // Sanitize filename to prevent path traversal
                $originalName = $file->getClientOriginalName();
                $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $filename = time() . '_' . $sanitizedName;
                
                Log::info('Starting file upload', [
                    'original_name' => $file->getClientOriginalName(),
                    'temp_path' => $file->getPathname(),
                    'temp_exists' => file_exists($file->getPathname()),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'error' => $file->getError(),
                    'is_valid' => $file->isValid()
                ]);
                
                // Store file in uploads directory using Storage facade
                $filePath = Storage::disk('local')->putFileAs('uploads', $file, $filename);
                
                if (!$filePath) {
                    throw new \Exception('Failed to store file. File path is null.');
                }
                
                $fullPath = Storage::disk('local')->path($filePath);
                
                Log::info('File upload attempt', [
                    'stored_path' => $filePath,
                    'full_path' => $fullPath,
                    'file_exists' => file_exists($fullPath),
                    'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0
                ]);
                
                if (!file_exists($fullPath)) {
                    throw new \Exception("File was not stored properly. Expected at: {$fullPath}");
                }
            }

            // Check if file is encrypted and decrypt if needed
            $csvContent = file_get_contents($fullPath);
            $isEncrypted = $this->csvEncryptionService->isEncrypted($originalName);
            $decryptedPath = null;
            
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

                $validation = $this->csvEncryptionService->validateEncryptedFile($filePath, $csvPassword);
                if (!$validation['valid']) {
                    DB::rollBack();
                    // Don't expose validation error details
                    Log::warning('CSV Decryption Failed', [
                        'error' => $validation['error'] ?? 'Unknown error'
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid password or corrupted encrypted file. Please check your password and try again.',
                        'requires_password' => true
                    ], 400);
                }

                // Write decrypted content to a temporary file for parsing
                $decryptedPath = storage_path('app/temp/' . uniqid('decrypted_') . '.csv');
                if (!is_dir(dirname($decryptedPath))) {
                    mkdir(dirname($decryptedPath), 0755, true);
                }
                file_put_contents($decryptedPath, $validation['content']);
                $fullPath = $decryptedPath;
            }

            // VALIDATE CSV COMPLETELY BEFORE CREATING BATCH
            // This ensures we catch all errors before any database records are created
            try {
                Log::info('Starting CSV validation before batch creation');
                
                // Parse and validate CSV file
                $parseResult = $this->parseCsvFile($fullPath);
                $recipients = $parseResult['recipients'];
                $skippedRows = $parseResult['skipped_rows'];

                // Validate that we have valid recipients
                if (empty($recipients)) {
                    DB::rollBack();
                    
                    // Clean up temp file if created
                    if ($decryptedPath && file_exists($decryptedPath)) {
                        unlink($decryptedPath);
                    }
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'CSV validation failed: No valid recipients found in CSV file. Please check your CSV format and data.',
                        'skipped_rows' => $skippedRows,
                        'validation_failed' => true
                    ], 422);
                }

                // Check for duplicate phone numbers in the batch (database constraint)
                $phoneNumbers = array_column($recipients, 'phone_number');
                $duplicatePhones = array_diff_assoc($phoneNumbers, array_unique($phoneNumbers));
                if (!empty($duplicatePhones)) {
                    DB::rollBack();
                    
                    // Clean up temp file if created
                    if ($decryptedPath && file_exists($decryptedPath)) {
                        unlink($decryptedPath);
                    }
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'CSV validation failed: Duplicate phone numbers found in the same batch. Each recipient must have a unique phone number.',
                        'duplicate_phones' => array_unique($duplicatePhones),
                        'validation_failed' => true
                    ], 422);
                }

                // Validate meter numbers if requested
                if ($request->input('validate_meters', false)) {
                    $meterValidationResults = $this->meterValidationService->validateMeterBatch(
                        array_map(function($recipient) {
                            return [
                                'meter_number' => $recipient['meter_number'],
                                'disco' => $recipient['disco']
                            ];
                        }, $recipients),
                        false // Don't use API validation by default (slower)
                    );

                    if (count($meterValidationResults['invalid']) > 0) {
                        DB::rollBack();
                        
                        // Clean up temp file if created
                        if ($decryptedPath && file_exists($decryptedPath)) {
                            unlink($decryptedPath);
                        }

                        return response()->json([
                            'success' => false,
                            'message' => 'CSV validation failed: Meter number validation failed. Please correct the errors before uploading.',
                            'meter_validation_errors' => $meterValidationResults['invalid'],
                            'validation_summary' => $meterValidationResults['summary'],
                            'validation_failed' => true
                        ], 422);
                    }
                }

                Log::info('CSV validation passed', [
                    'valid_recipients' => count($recipients),
                    'skipped_rows' => $skippedRows,
                    'total_amount' => array_sum(array_column($recipients, 'amount'))
                ]);

            } catch (\Exception $validationError) {
                DB::rollBack();
                
                // Clean up temp file if created
                if ($decryptedPath && file_exists($decryptedPath)) {
                    unlink($decryptedPath);
                }
                
                Log::error('CSV validation failed', [
                    'error' => $validationError->getMessage(),
                    'trace' => $validationError->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'CSV validation failed: ' . $validationError->getMessage(),
                    'validation_failed' => true
                ], 422);
            }

            // Clean up temp file if created (validation passed, now we can proceed)
            if ($decryptedPath && file_exists($decryptedPath)) {
                unlink($decryptedPath);
            }

            // CSV validation passed - now create batch upload record
            // Status is 'uploaded' - user will manually start processing
            // For SharePoint files, store the original filename; for regular uploads, use sanitized filename
            $storedFilename = $isSharePoint ? $originalName : $filename;
            
            $batchUpload = BatchUpload::create([
                'filename' => $storedFilename,
                'batch_name' => $request->batch_name ?? 'Batch ' . date('Y-m-d H:i:s'),
                'total_recipients' => count($recipients),
                'total_amount' => array_sum(array_column($recipients, 'amount')),
                'status' => 'uploaded', // User will manually start processing
                'sms_template' => $request->input('sms_template'),
                'email_template' => $request->input('email_template'),
                'enable_sms' => $request->has('enable_sms') ? filter_var($request->input('enable_sms'), FILTER_VALIDATE_BOOLEAN) : true,
                'enable_email' => $request->has('enable_email') ? filter_var($request->input('enable_email'), FILTER_VALIDATE_BOOLEAN) : true,
                'user_id' => auth()->id()
            ]);
            
            Log::info('Batch created successfully after CSV validation', [
                'batch_id' => $batchUpload->id,
                'total_recipients' => count($recipients)
            ]);

            // Create recipient records
            foreach ($recipients as $recipientData) {
                Recipient::create([
                    'batch_upload_id' => $batchUpload->id,
                    'name' => $recipientData['name'],
                    'customer_name' => $recipientData['customer_name'],
                    'address' => $recipientData['address'],
                    'phone_number' => $recipientData['phone_number'],
                    'email' => $recipientData['email'] ?? null,
                    'disco' => $recipientData['disco'],
                    'meter_number' => $recipientData['meter_number'],
                    'meter_type' => $recipientData['meter_type'],
                    'amount' => $recipientData['amount'],
                ]);
            }

            DB::commit();

            // Log batch creation activity
            try {
                app(\App\Services\ActivityLoggingService::class)->logBatchCreated($batchUpload, $user);
            } catch (\Exception $e) {
                Log::warning('Failed to log batch creation activity', ['error' => $e->getMessage()]);
            }

            // CSV validation passed and batch created - user can now start token distribution manually
            Log::info('Batch created successfully after CSV validation - ready for manual processing', [
                'batch_id' => $batchUpload->id,
                'total_recipients' => count($recipients),
                'status' => 'uploaded'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'CSV file uploaded and validated successfully! ' . count($recipients) . ' valid recipients found. Click "Start Processing" to begin token distribution.',
                'batch_id' => $batchUpload->id,
                'batch_status' => 'uploaded',
                'total_recipients' => count($recipients),
                'total_amount' => array_sum(array_column($recipients, 'amount')),
                'skipped_rows' => $skippedRows ?? 0,
                'valid_rows' => count($recipients),
                'ready_for_processing' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            // Clean up SharePoint temp file if exists
            if (isset($fullPath) && $isSharePoint && file_exists($fullPath)) {
                @unlink($fullPath);
            }
            
            // Log detailed error for debugging (not exposed to user)
            Log::error('CSV Upload Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $originalName ?? ($request->hasFile('csv_file') ? $request->file('csv_file')->getClientOriginalName() : 'unknown'),
                'is_sharepoint' => $isSharePoint ?? false
            ]);

            // Don't expose sensitive error details to users
            return response()->json([
                'success' => false,
                'message' => 'Upload failed. Please check your file and try again.'
            ], 500);
        } finally {
            // Clean up SharePoint temp file after successful processing
            if (isset($fullPath) && isset($isSharePoint) && $isSharePoint && file_exists($fullPath)) {
                @unlink($fullPath);
            }
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
            // Find batch - allow 'uploaded' status (for manual processing) or 'failed' status (for retry)
            $batch = BatchUpload::with('recipients')
                ->where('id', $batchId)
                ->whereIn('status', ['uploaded', 'failed'])
                ->first();

            if (!$batch) {
                $existingBatch = BatchUpload::find($batchId);
                if ($existingBatch) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Batch is already ' . $existingBatch->status . '. Only batches with status "uploaded" or "failed" can be processed.',
                        'current_status' => $existingBatch->status
                    ], 422);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Batch not found'
                ], 404);
            }

            // Check if batch already has recipients
            if ($batch->recipients->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch has no recipients to process'
                ], 422);
            }

            // Update batch status to processing
            $batch->update(['status' => 'processing']);

            Log::info('Starting manual batch processing', [
                'batch_id' => $batch->id,
                'total_recipients' => $batch->total_recipients,
                'previous_status' => $batch->getOriginal('status')
            ]);

            // Start processing synchronously
            $this->startBatchProcessing($batch);

            // Reload batch to get latest status
            $batch->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Token distribution started! Processing ' . $batch->total_recipients . ' recipients synchronously in batches...',
                'batch_id' => $batchId,
                'batch_status' => $batch->status,
                'total_recipients' => $batch->total_recipients,
                'processing_mode' => 'synchronous_batch',
                'batch_size' => config('buypower.batch_size', 5)
            ]);

        } catch (\Exception $e) {
            Log::error('Batch Processing Error', [
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update batch status to failed if it's still processing
            $batch = BatchUpload::find($batchId);
            if ($batch && $batch->status === 'processing') {
                $batch->update([
                    'status' => 'failed',
                    'error_message' => 'Processing failed: ' . $e->getMessage()
                ]);
            }

            // Don't expose sensitive error details to users
            Log::error('Batch Processing Error', [
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start processing. Please try again or contact support.'
            ], 500);
        }
    }

    /**
     * Get batch status
     */
    public function getBatchStatus($batchId)
    {
        // Validate batch ID to prevent SQL injection
        $validator = Validator::make(['batch_id' => $batchId], [
            'batch_id' => 'required|integer|exists:batch_uploads,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid batch ID'
            ], 422);
        }

        $batch = BatchUpload::with(['recipients' => function($query) {
            $query->selectRaw('batch_upload_id, status, count(*) as count')
                  ->groupBy('batch_upload_id', 'status');
        }])->findOrFail($batchId);

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
    public function history(Request $request)
    {
        // Get filter parameters
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $meterNumber = $request->get('meter_number');
        $disco = $request->get('disco');
        $searchQuery = $request->get('search', '');

        // Build query
        $query = BatchUpload::with(['recipients', 'transactions']);

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

        // Filter by recipient criteria (meter number, disco) - sanitize inputs
        if (!empty($meterNumber) || !empty($disco)) {
            $query->whereHas('recipients', function($q) use ($meterNumber, $disco) {
                if (!empty($meterNumber)) {
                    $sanitizedMeter = $this->sanitizeSearchInput($meterNumber);
                    $q->where('meter_number', 'like', "%{$sanitizedMeter}%");
                }
                if (!empty($disco)) {
                    $validDiscos = ['EKO', 'IKEJA', 'ABUJA', 'IBADAN', 'ENUGU', 'PH', 'JOS', 'KADUNA', 'KANO', 'BH'];
                    $discoValue = strtoupper(trim($disco));
                    if (in_array($discoValue, $validDiscos)) {
                        $q->where('disco', $discoValue);
                    }
                }
            });
        }

        // Status filter - validate enum values
        $status = $request->get('status');
        if (!empty($status)) {
            $validStatuses = ['uploaded', 'processing', 'completed', 'failed'];
            if (in_array($status, $validStatuses)) {
                $query->where('status', $status);
            }
        }

        // Get distinct discos for filter dropdown
        $availableDiscos = \App\Models\Recipient::distinct()
            ->whereNotNull('disco')
            ->where('disco', '!=', '')
            ->orderBy('disco')
            ->pluck('disco')
            ->filter()
            ->values();

        $batches = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());

        return view('bulk-token.history', compact('batches', 'availableDiscos'));
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

        $batch = BatchUpload::with(['recipients', 'transactions'])
            ->findOrFail($batchId);

        return view('bulk-token.show', compact('batch'));
    }

    /**
     * View transaction history
     */
    public function transactions(Request $request)
    {
        // Get filter parameters
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $meterNumber = $request->get('meter_number');
        $disco = $request->get('disco');
        $recipientName = $request->get('recipient_name');
        $searchQuery = $request->get('search', '');
        $statusFilter = $request->get('status');

        // Build query
        $query = Transaction::with(['recipient', 'batchUpload']);

        // Search query filter - sanitize user input
        if (!empty($searchQuery)) {
            $sanitizedQuery = $this->sanitizeSearchInput($searchQuery);
            $query->where(function($q) use ($sanitizedQuery) {
                $q->where('phone_number', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('buypower_reference', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('order_id', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('token', 'like', "%{$sanitizedQuery}%")
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

        // Meter number filter - sanitize
        if (!empty($meterNumber)) {
            $sanitizedMeter = $this->sanitizeSearchInput($meterNumber);
            $query->whereHas('recipient', function($q) use ($sanitizedMeter) {
                $q->where('meter_number', 'like', "%{$sanitizedMeter}%");
            });
        }

        // Disco filter - validate enum values
        if (!empty($disco)) {
            $validDiscos = ['EKO', 'IKEJA', 'ABUJA', 'IBADAN', 'ENUGU', 'PH', 'JOS', 'KADUNA', 'KANO', 'BH'];
            $discoValue = strtoupper(trim($disco));
            if (in_array($discoValue, $validDiscos)) {
                $query->whereHas('recipient', function($q) use ($discoValue) {
                    $q->where('disco', $discoValue);
                });
            }
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

        // Get distinct discos for filter dropdown
        $availableDiscos = Recipient::distinct()
            ->whereNotNull('disco')
            ->where('disco', '!=', '')
            ->orderBy('disco')
            ->pluck('disco')
            ->filter()
            ->values();

        $transactions = $query->orderBy('processed_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->appends($request->query());

        // Calculate stats (filtered if filters are applied)
        $statsQuery = Transaction::query();
        if (!empty($statusFilter)) {
            $statsQuery->where('status', $statusFilter);
        }
        
        $stats = [
            'successful' => Transaction::where('status', 'success')->count(),
            'failed' => Transaction::where('status', 'failed')->count(),
            'total_amount' => Transaction::where('status', 'success')->sum('amount'),
        ];
        $total = $stats['successful'] + $stats['failed'];
        $stats['success_rate'] = $total > 0 ? ($stats['successful'] / $total) * 100 : 0;

        return view('bulk-token.transactions', compact('transactions', 'stats', 'availableDiscos'));
    }

    /**
     * Show transaction details
     */
    public function showTransaction($transactionId)
    {
        // Validate transaction ID to prevent SQL injection
        $validator = Validator::make(['transaction_id' => $transactionId], [
            'transaction_id' => 'required|integer|exists:transactions,id'
        ]);

        if ($validator->fails()) {
            abort(404, 'Transaction not found');
        }

        $transaction = Transaction::with(['recipient', 'batchUpload'])->findOrFail($transactionId);
        
        return view('bulk-token.transaction-details', compact('transaction'));
    }

    /**
     * Download token details
     */
    public function downloadToken($transactionId)
    {
        // Validate transaction ID to prevent SQL injection
        $validator = Validator::make(['transaction_id' => $transactionId], [
            'transaction_id' => 'required|integer|exists:transactions,id'
        ]);

        if ($validator->fails()) {
            abort(404, 'Transaction not found');
        }

        $transaction = Transaction::with(['recipient', 'batchUpload'])->findOrFail($transactionId);
        
        if (!$transaction->token) {
            return response()->json(['error' => 'No token available for this transaction'], 404);
        }

        $recipient = $transaction->recipient;
        $batch = $transaction->batchUpload;
        
        $content = "╔════════════════════════════════════════════════════════════╗\n";
        $content .= "║     WINIT PRIZE DISTRIBUTION - ELECTRICITY TOKEN RECEIPT ║\n";
        $content .= "╚════════════════════════════════════════════════════════════╝\n\n";
        
        $content .= "RECIPIENT INFORMATION\n";
        $content .= "─────────────────────────────────────────────────────────────\n";
        $content .= "Name:           " . ($recipient->name ?? 'N/A') . "\n";
        $content .= "Phone Number:   " . ($transaction->phone_number ?? 'N/A') . "\n";
        if ($recipient->address) {
            $content .= "Address:        " . $recipient->address . "\n";
        }
        $content .= "\n";
        
        $content .= "METER INFORMATION\n";
        $content .= "─────────────────────────────────────────────────────────────\n";
        $content .= "Meter Number:   " . ($recipient->meter_number ?? 'N/A') . "\n";
        $content .= "Meter Type:     " . ucfirst($recipient->meter_type ?? 'prepaid') . "\n";
        $content .= "Disco:          " . ($recipient->disco ?? 'N/A') . "\n";
        $content .= "\n";
        
        $content .= "TOKEN DETAILS\n";
        $content .= "─────────────────────────────────────────────────────────────\n";
        $content .= "Token:          " . $transaction->token . "\n";
        $content .= "Amount:         ₦" . number_format($transaction->amount, 2) . "\n";
        $content .= "Units:          " . ($transaction->units ? $transaction->units . ' KWh' : 'N/A') . "\n";
        $content .= "\n";
        
        $content .= "TRANSACTION INFORMATION\n";
        $content .= "─────────────────────────────────────────────────────────────\n";
        $content .= "Reference:      " . ($transaction->buypower_reference ?? 'N/A') . "\n";
        if ($transaction->order_id) {
            $content .= "Order ID:       " . $transaction->order_id . "\n";
        }
        $content .= "Status:         " . strtoupper($transaction->status) . "\n";
        $content .= "Date:           " . ($transaction->processed_at ? $transaction->processed_at->format('M d, Y h:i A') : 'N/A') . "\n";
        if ($batch) {
            $content .= "Batch:          " . ($batch->batch_name ?? 'N/A') . "\n";
        }
        $content .= "\n";
        
        $content .= "╔════════════════════════════════════════════════════════════╗\n";
        $content .= "║          Thank you for using WinIt Prize Distribution     ║\n";
        $content .= "╚════════════════════════════════════════════════════════════╝\n";

        $filename = 'winit-token-' . $transactionId . '-' . date('Y-m-d-His') . '.txt';
        
        return response($content)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Download batch report as CSV
     */
    public function downloadBatchReport($batchId)
    {
        // Validate batch ID to prevent SQL injection
        $validator = Validator::make(['batch_id' => $batchId], [
            'batch_id' => 'required|integer|exists:batch_uploads,id'
        ]);

        if ($validator->fails()) {
            abort(404, 'Batch not found');
        }

        $batch = BatchUpload::with(['recipients', 'transactions'])
            ->findOrFail($batchId);

        // Create CSV content
        $filename = 'batch-report-' . $batch->id . '-' . date('Y-m-d-His') . '.csv';
        
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
                'Meter Number',
                'Disco',
                'Amount (₦)',
                'Status',
                'Token',
                'Units (KWh)',
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
                    $recipient->meter_number ?? '',
                    $recipient->disco ?? '',
                    number_format($recipient->amount, 2),
                    strtoupper($recipient->status ?? 'pending'),
                    $transaction && isset($transaction->token) ? $transaction->token : 'N/A',
                    $transaction && isset($transaction->units) ? $transaction->units : 'N/A',
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
            $header = fgetcsv($handle, 0, ',', '"', '\\');
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
            $optionalColumns = ['customer_name', 'email'];
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
            
            while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
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
                $email = trim($rowData['email'] ?? '') ?: null;
                $disco = strtoupper(trim($rowData['disco'] ?? ''));
                $meterNumber = trim($rowData['meter_number'] ?? '');
                $meterType = strtolower(trim($rowData['meter_type'] ?? 'prepaid'));
                $customerName = trim($rowData['customer_name'] ?? '') ?: null;
                $amount = floatval($rowData['amount'] ?? 0);
                
                // Validate email if provided
                if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Log::warning("Skipping row {$lineNumber} - invalid email format", [
                        'email' => $email,
                        'row' => $rowData
                    ]);
                    $skippedRows++;
                    continue;
                }

                // Use custom validation rules
                $validator = Validator::make([
                    'phone_number' => $phoneNumber,
                    'meter_number' => $meterNumber,
                    'disco' => $disco,
                    'amount' => $amount
                ], [
                    'phone_number' => [new NigerianPhoneNumber()],
                    'meter_number' => [new MeterNumber()],
                    'disco' => [new ValidDiscoCode()],
                    'amount' => [new ValidAmount()]
                ]);

                if ($validator->fails()) {
                    Log::warning("Skipping row {$lineNumber} - validation failed", [
                        'errors' => $validator->errors()->toArray(),
                        'row' => $rowData
                    ]);
                    $skippedRows++;
                    continue;
                }
                
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
                
                // Validate meter type
                $validMeterTypes = ['prepaid', 'postpaid'];
                if (!in_array($meterType, $validMeterTypes)) {
                    Log::warning("Invalid meter type at line {$lineNumber}: {$meterType}. Valid types: prepaid, postpaid");
                    $skippedRows++;
                    continue;
                }

                $recipients[] = [
                    'name' => $name,
                    'customer_name' => $customerName,
                    'address' => $address,
                    'phone_number' => $phoneNumber,
                    'email' => $email,
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
        
        return [
            'recipients' => $recipients,
            'skipped_rows' => $skippedRows
        ];
    }

    /**
     * Start batch processing for token distribution (synchronous processing)
     */
    protected function startBatchProcessing(BatchUpload $batch)
    {
        try {
            // Process batch immediately (synchronously) instead of queuing
            Log::info("Starting synchronous batch processing", [
                'batch_id' => $batch->id,
                'total_recipients' => $batch->total_recipients
            ]);

            // Reload batch to ensure we have the latest data
            $batch->refresh();

            // Create job instance and execute immediately
            $job = new ProcessBatchJob($batch);
            
            // Set maximum execution time to prevent timeouts (but may still timeout on large batches)
            set_time_limit(300); // 5 minutes max
            
            // Execute processing
            $job->handle();

            // Reload batch to get final status
            $batch->refresh();

            Log::info("Batch processing completed synchronously", [
                'batch_id' => $batch->id,
                'total_recipients' => $batch->total_recipients,
                'final_status' => $batch->status
            ]);
        } catch (\Exception $e) {
            Log::error("Synchronous batch processing failed", [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update batch status to failed only if it's still in processing
            $batch->refresh();
            if ($batch->status === 'processing') {
                $batch->update([
                    'status' => 'failed',
                    'error_message' => 'Batch processing failed: ' . $e->getMessage()
                ]);
            }

            // Don't rethrow - let the caller handle it gracefully
            throw $e;
        }
    }

    /**
     * Download sample CSV file
     */
    public function downloadSample()
    {
        $samplePath = base_path('sample_recipients.csv');
        
        if (!file_exists($samplePath)) {
            abort(404, 'Sample CSV file not found');
        }

        return response()->download($samplePath, 'sample_recipients.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sample_recipients.csv"'
        ]);
    }

    /**
     * Get updated batch statuses (AJAX endpoint for real-time updates)
     */
    public function statusUpdate(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'batch_ids' => 'required|array|min:1|max:50',
            'batch_ids.*' => 'required|integer|exists:batch_uploads,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid batch IDs provided',
                'errors' => $validator->errors()
            ], 422);
        }

        $batchIds = $request->input('batch_ids', []);

        $batches = BatchUpload::whereIn('id', $batchIds)
            ->select('id', 'status')
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'status' => $batch->status,
                ];
            });

        return response()->json([
            'success' => true,
            'batches' => $batches
        ]);
    }

    /**
     * Sanitize search input to prevent SQL injection
     */
    protected function sanitizeSearchInput(string $input): string
    {
        // Remove SQL injection patterns
        $input = trim($input);
        $input = preg_replace('/[%_]/', '', $input); // Remove LIKE wildcards
        $input = preg_replace('/[^a-zA-Z0-9\s\-@.]/', '', $input); // Allow only alphanumeric, spaces, hyphens, @, and dots
        $input = mb_substr($input, 0, 255); // Limit length
        return $input;
    }
}
