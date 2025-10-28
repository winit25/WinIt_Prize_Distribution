@extends('layouts.sidebar')

@section('title', 'Bulk Token Dashboard')

@push('styles')
<style>
    .upload-area {
        border: 2px dashed var(--winit-border);
        border-radius: 1rem;
        padding: 3rem 2rem;
        text-align: center;
        background: linear-gradient(135deg, var(--winit-blue-50) 0%, var(--winit-light) 100%);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .upload-area:hover {
        border-color: var(--winit-primary);
        background: linear-gradient(135deg, var(--winit-blue-100) 0%, var(--winit-blue-50) 100%);
        transform: translateY(-2px);
    }

    .upload-area.dragover {
        border-color: var(--winit-primary);
        background: linear-gradient(135deg, var(--winit-blue-100) 0%, var(--winit-blue-50) 100%);
        transform: scale(1.02);
    }

    .metric-card {
        background: linear-gradient(135deg, var(--winit-primary) 0%, var(--winit-primary-dark) 100%);
        border-radius: 1rem;
        color: white;
        text-align: center;
        transition: all 0.3s ease;
    }

    .metric-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(30, 58, 138, 0.3);
    }

    .form-control {
        border: 2px solid var(--winit-border);
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        font-family: 'Montserrat', sans-serif;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--winit-primary);
        box-shadow: 0 0 0 0.2rem rgba(18, 18, 104, 0.25);
    }

    .alert {
        border-radius: 1rem;
        border: none;
        font-family: 'Montserrat', sans-serif;
    }

    .alert-info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: var(--winit-primary-dark);
    }

    .alert-warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border: 1px solid #f59e0b;
    }

    .progress {
        height: 0.75rem;
        border-radius: 0.5rem;
        background: var(--winit-border);
    }

    .progress-bar {
        background: linear-gradient(135deg, var(--winit-primary) 0%, var(--winit-primary-dark) 100%);
        border-radius: 0.5rem;
    }

    .btn-outline-primary {
        border: 2px solid var(--winit-primary);
        color: var(--winit-primary);
        background: transparent;
        border-radius: 0.75rem;
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background: var(--winit-primary);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(18, 18, 104, 0.3);
    }

    .notification-item {
        transition: all 0.3s ease;
        border-left: 4px solid var(--winit-primary);
    }

    .notification-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .notification-item.unread {
        border-left-color: var(--winit-danger);
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    }

    .modal-content {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background: linear-gradient(135deg, var(--winit-primary) 0%, var(--winit-primary-dark) 100%);
        color: white;
        border-radius: 1rem 1rem 0 0;
        border: none;
    }

    .modal-title {
        font-weight: 600;
        font-family: 'Montserrat', sans-serif;
    }

    .btn-close {
        filter: invert(1);
    }
</style>
@endpush

@section('content')
<div class="row">
    <!-- Upload Section -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-upload"></i>
                    Upload Recipients CSV
                </h5>
            </div>
            <div class="card-body">
                <!-- CSV Format Info -->
                <div class="alert alert-info mb-4">
                    <h6><i class="fas fa-info-circle"></i> CSV Format Requirements:</h6>
                    <p class="mb-2">Your CSV file must contain the following columns (exact names):</p>
                    <ul class="mb-2">
                        <li><strong>name</strong> - Recipient's full name</li>
                        <li><strong>address</strong> - Recipient's billing address</li>
                        <li><strong>phone_number</strong> - Nigerian phone number (e.g., 08036120008)</li>
                        <li><strong>disco</strong> - Distribution company code (EKO, IKEJA, ABUJA, IBADAN, ENUGU, PH, JOS, KADUNA, KANO, BH)</li>
                        <li><strong>meter_number</strong> - Electricity meter number (11 digits, e.g., 12345678910)</li>
                        <li><strong>meter_type</strong> - Meter type (prepaid or postpaid)</li>
                        <li><strong>amount</strong> - Token amount in Naira (e.g., 1000.00)</li>
                        <li><strong>customer_name</strong> - <em>Optional:</em> Customer name if different from recipient</li>
                    </ul>
                    <div class="alert alert-warning mt-3">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Important Notes:</h6>
                        <ul class="mb-0">
                            <li><strong>Phone Number:</strong> Must be 11 digits starting with 080, 081, 070, 090, or 091</li>
                            <li><strong>Meter Number:</strong> Must be exactly 11 digits</li>
                            <li><strong>Disco Codes:</strong> Use the exact codes listed above (not EKEDC, IKEDC, etc.)</li>
                            <li><strong>Amount:</strong> Minimum ₦100, Maximum ₦100,000 per transaction</li>
                        </ul>
                    </div>
                    <div class="mt-3">
                        <a href="/sample-csv" class="btn btn-outline-primary btn-sm" download="sample_recipients.csv">
                            <i class="fas fa-download me-2"></i> Download Sample CSV
                        </a>
                        <button type="button" class="btn btn-outline-info btn-sm ms-2" onclick="showQuickNotifications()">
                            <i class="fas fa-bell me-2"></i> Notifications
                            <span class="badge bg-danger ms-1" id="uploadNotificationBadge" style="display: none;">0</span>
                        </button>
                    </div>
                </div>

                <!-- Real-time Status Bar -->
                <div class="alert alert-info d-flex align-items-center mb-4" id="uploadStatusAlert">
                    <div class="spinner-border spinner-border-sm me-3" role="status" id="uploadStatusSpinner">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="flex-grow-1">
                        <strong id="uploadStatusTitle">System Ready</strong>
                        <div id="uploadStatusMessage" class="small mt-1">Ready to process your CSV upload</div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted" id="uploadStatusTime">--:--</small>
                    </div>
                </div>

                <!-- Upload Form -->
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">CSV File</label>
                            <div class="upload-area" id="uploadArea">
                                <input type="file" 
                                       id="csvFile" 
                                       name="csv_file" 
                                       accept=".csv,.txt,text/csv,application/csv"
                                       class="d-none"
                                       required>
                                <div id="uploadContent">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <p class="mb-2">Drag and drop your CSV file here</p>
                                    <p class="text-muted">or <button type="button" class="btn btn-link p-0" onclick="document.getElementById('csvFile').click()">browse files</button></p>
                                </div>
                            </div>
                            <div id="fileInfo" class="mt-2 d-none">
                                <small class="text-success">
                                    <i class="fas fa-file-csv"></i>
                                    <span id="fileName"></span>
                                    <span id="fileSize" class="text-muted"></span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Batch Name (Optional)</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="batch_name" 
                                   placeholder="e.g., Monthly Distribution">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg" id="uploadBtn" disabled>
                        <i class="fas fa-upload"></i>
                        Upload & Parse CSV
                    </button>
                </form>

                <!-- Upload Progress -->
                <div id="uploadProgress" class="mt-4 d-none">
                    <h6>Processing...</h6>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 100%"></div>
                    </div>
                </div>

                <!-- Upload Results -->
                <div id="uploadResults" class="mt-4 d-none">
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle"></i> Upload Successful!</h6>
                        <p class="mb-2">Your CSV has been processed:</p>
                        <ul class="mb-0">
                            <li><strong id="totalRecipients"></strong> recipients found</li>
                            <li><strong id="totalAmount"></strong> total amount</li>
                        </ul>
                    </div>
                    
                    <button type="button" class="btn btn-success btn-lg" id="processBtn">
                        <i class="fas fa-play"></i>
                        Start Token Distribution
                    </button>
                </div>

                <!-- Processing Status -->
                <div id="processingStatus" class="mt-4 d-none">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6><i class="fas fa-cog fa-spin"></i> Processing Batch...</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="progress mb-2">
                                        <div id="processingProgress" 
                                             class="progress-bar bg-info" 
                                             role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <small class="text-muted">
                                        <span id="progressText">0%</span> completed
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between">
                                        <small><strong id="successCount">0</strong> successful</small>
                                        <small><strong id="failedCount">0</strong> failed</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Batches -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-clock"></i>
                    Recent Batches
                </h6>
            </div>
            <div class="card-body">
                @if($recentBatches && $recentBatches->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentBatches as $batch)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $batch->batch_name }}</h6>
                                        <p class="mb-1 small text-muted">
                                            {{ $batch->total_recipients }} recipients • 
                                            {{ number_format($batch->total_amount, 2) }} NGN
                                        </p>
                                        <small class="text-muted">
                                            {{ $batch->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <span class="status-badge status-{{ $batch->status }}">
                                        {{ ucfirst($batch->status) }}
                                    </span>
                                </div>
                                @if($batch->status == 'processing' || $batch->status == 'completed')
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar bg-{{ $batch->status == 'completed' ? 'success' : 'info' }}" 
                                             style="width: {{ $batch->completion_percentage }}%"></div>
                                    </div>
                                @endif
                                <div class="mt-2">
                                    <a href="{{ route('bulk-token.show', $batch->id) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="{{ route('bulk-token.history') }}" class="btn btn-outline-primary btn-sm">
                            View All History
                        </a>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No recent batches</p>
                        <small>Upload your first CSV to get started</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-bar"></i>
                    Quick Stats
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="metric-card card text-white mb-2">
                            <div class="card-body py-3">
                                <h3 class="mb-0">{{ $recentBatches ? $recentBatches->where('status', 'completed')->count() : 0 }}</h3>
                                <small>Completed</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-info text-white mb-2">
                            <div class="card-body py-3 text-center">
                                <h3 class="mb-0">{{ $recentBatches ? $recentBatches->where('status', 'processing')->count() : 0 }}</h3>
                                <small>Processing</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Notification Modal -->
<div class="modal fade" id="quickNotificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-bell me-2"></i>
                    Recent Notifications
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="quickNotificationContent">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading notifications...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="/notifications" class="btn btn-primary">View All Notifications</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentBatchId = null;
    
    // Utility functions
    const utils = {
        showAlert: function(type, message) {
            // Create alert element
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            // Insert at the top of the form
            const form = document.getElementById('uploadForm');
            if (form) {
                form.insertBefore(alertDiv, form.firstChild);
                
                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);
            }
        }
    };
    
    // Upload functionality
    document.addEventListener('DOMContentLoaded', function() {
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('csvFile');
        const uploadForm = document.getElementById('uploadForm');
        const uploadBtn = document.getElementById('uploadBtn');
        
        // File upload area interactions
        uploadArea.addEventListener('click', () => fileInput.click());
        uploadArea.addEventListener('dragover', handleDragOver);
        uploadArea.addEventListener('dragleave', handleDragLeave);
        uploadArea.addEventListener('drop', handleDrop);
        fileInput.addEventListener('change', handleFileSelect);
        
        // Form submission
        uploadForm.addEventListener('submit', handleUpload);
        
        // Process button
        document.addEventListener('click', function(e) {
            if (e.target.id === 'processBtn') {
                startProcessing();
            }
        });
    });
    
    function handleDragOver(e) {
        e.preventDefault();
        document.getElementById('uploadArea').classList.add('dragover');
    }
    
    function handleDragLeave(e) {
        e.preventDefault();
        document.getElementById('uploadArea').classList.remove('dragover');
    }
    
    function handleDrop(e) {
        e.preventDefault();
        document.getElementById('uploadArea').classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            document.getElementById('csvFile').files = files;
            handleFileSelect();
        }
    }
    
    function handleFileSelect() {
        const fileInput = document.getElementById('csvFile');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const uploadBtn = document.getElementById('uploadBtn');
        
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            
            console.log('File selected:', {
                name: file.name,
                size: file.size,
                type: file.type,
                lastModified: file.lastModified
            });
            
            // Validate file type
            const allowedTypes = ['text/csv', 'application/csv', 'text/plain', 'application/vnd.ms-excel'];
            const allowedExtensions = ['.csv', '.txt'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
            
            if (!allowedTypes.includes(file.type) && !allowedExtensions.includes(fileExtension)) {
                utils.showAlert('danger', 'Please select a valid CSV file (.csv or .txt)');
                fileInput.value = '';
                return;
            }
            
            // Validate file size (5MB max)
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                utils.showAlert('danger', 'File size must be less than 5MB');
                fileInput.value = '';
                return;
            }
            
            // Show file info
            fileName.textContent = file.name;
            fileSize.textContent = `(${formatFileSize(file.size)})`;
            fileInfo.classList.remove('d-none');
            uploadBtn.disabled = false;
            
            // Show success message
            utils.showAlert('success', `File "${file.name}" selected successfully. Click "Upload & Parse CSV" to proceed.`);
        } else {
            fileInfo.classList.add('d-none');
            uploadBtn.disabled = true;
        }
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    function handleUpload(e) {
        e.preventDefault();
        
        const fileInput = document.getElementById('csvFile');
        if (!fileInput.files.length) {
            utils.showAlert('danger', 'Please select a CSV file to upload');
            return;
        }
        
        const file = fileInput.files[0];
        console.log('Uploading file:', {
            name: file.name,
            size: file.size,
            type: file.type
        });
        
        const formData = new FormData(e.target);
        
        // Log FormData contents
        for (let [key, value] of formData.entries()) {
            console.log('FormData:', key, value);
        }
        
        const uploadProgress = document.getElementById('uploadProgress');
        const uploadResults = document.getElementById('uploadResults');
        
        // Show progress
        uploadProgress.classList.remove('d-none');
        document.getElementById('uploadBtn').disabled = true;
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value || 
                         window.csrfToken;
        
        console.log('CSRF Token:', csrfToken);
        console.log('Upload URL:', '{{ route("bulk-token.upload") }}');
        
        fetch('{{ route("bulk-token.upload") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            uploadProgress.classList.add('d-none');
            
            if (data.success) {
                // Show success results
                document.getElementById('totalRecipients').textContent = data.total_recipients + ' recipients';
                document.getElementById('totalAmount').textContent = utils.formatCurrency(data.total_amount);
                uploadResults.classList.remove('d-none');
                currentBatchId = data.batch_id;
                
                // Show detailed success message
                let successMessage = `✅ CSV uploaded successfully!<br>`;
                successMessage += `📊 <strong>${data.valid_rows}</strong> valid recipients found<br>`;
                successMessage += `💰 Total amount: <strong>${utils.formatCurrency(data.total_amount)}</strong><br>`;
                if (data.skipped_rows > 0) {
                    successMessage += `⚠️ <strong>${data.skipped_rows}</strong> rows skipped due to validation errors<br>`;
                }
                successMessage += `<br>Click "Start Processing" to distribute tokens.`;
                
                utils.showAlert('success', successMessage);
            } else {
                let errorMessage = 'Upload failed: ';
                if (data.errors) {
                    // Handle validation errors
                    errorMessage += Object.values(data.errors).flat().join(', ');
                } else {
                    errorMessage += (data.message || 'Unknown error');
                }
                utils.showAlert('danger', errorMessage);
                document.getElementById('uploadBtn').disabled = false;
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            uploadProgress.classList.add('d-none');
            utils.showAlert('danger', 'Upload failed: ' + error.message);
            document.getElementById('uploadBtn').disabled = false;
        });
    }
    
    function startProcessing() {
        if (!currentBatchId) return;
        
        const processingStatus = document.getElementById('processingStatus');
        const processBtn = document.getElementById('processBtn');
        
        processBtn.disabled = true;
        processingStatus.classList.remove('d-none');
        
        fetch(`/bulk-token/process/${currentBatchId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show detailed success message
                let successMessage = `🚀 <strong>Token Distribution Started!</strong><br>`;
                successMessage += `📊 Processing <strong>${data.total_recipients}</strong> recipients<br>`;
                successMessage += `⏳ This may take a few minutes to complete<br>`;
                successMessage += `<br>You can monitor progress in the History page.`;
                
                utils.showAlert('success', successMessage);
                
                // Start polling for progress
                pollProcessingStatus();
            } else {
                utils.showAlert('danger', 'Failed to start processing: ' + data.message);
                processBtn.disabled = false;
                processingStatus.classList.add('d-none');
            }
        })
        .catch(error => {
            utils.showAlert('danger', 'Failed to start processing: ' + error.message);
            processBtn.disabled = false;
            processingStatus.classList.add('d-none');
        });
    }
    
    function pollProcessingStatus() {
        if (!currentBatchId) return;
        
        const poll = () => {
            fetch(`/bulk-token/status/${currentBatchId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateProgressUI(data.data);
                        
                        if (data.data.status === 'processing') {
                            setTimeout(poll, 3000); // Poll every 3 seconds
                        } else if (data.data.status === 'completed') {
                            showCompletionMessage(data.data);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error polling status:', error);
                });
        };
        
        poll();
    }
    
    function updateProgressUI(data) {
        const progressBar = document.getElementById('processingProgress');
        const progressText = document.getElementById('progressText');
        const successCount = document.getElementById('successCount');
        const failedCount = document.getElementById('failedCount');
        
        progressBar.style.width = data.completion_percentage + '%';
        progressText.textContent = data.completion_percentage + '%';
        successCount.textContent = data.successful_transactions;
        failedCount.textContent = data.failed_transactions;
    }
    
    function showCompletionMessage(data) {
        document.getElementById('processingStatus').classList.add('d-none');
        
        const successRate = Math.round((data.successful_transactions / data.total_recipients) * 100);
        
        utils.showAlert('success', 
            `Processing completed! ${data.successful_transactions}/${data.total_recipients} tokens sent successfully (${successRate}% success rate)`
        );
        
        // Refresh page after 3 seconds
        setTimeout(() => location.reload(), 3000);
    }
    
    // Sample CSV download is now handled by direct link

    // Notification Integration
    function showNotificationCenter() {
        window.location.href = '/notifications';
    }

    function showQuickNotifications() {
        const modal = new bootstrap.Modal(document.getElementById('quickNotificationModal'));
        modal.show();
        
        // Load recent notifications
        loadQuickNotifications();
    }

    function loadQuickNotifications() {
        const content = document.getElementById('quickNotificationContent');
        
        fetch('/api/notifications')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.notifications.length > 0) {
                    const notifications = data.notifications.slice(0, 5); // Show only 5 recent
                    let html = '';
                    
                    notifications.forEach(notification => {
                        const timeAgo = getTimeAgo(notification.created_at);
                        const isUnread = !notification.read_at;
                        
                        html += `
                            <div class="notification-item mb-3 p-3 border rounded ${isUnread ? 'bg-light' : ''}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 ${isUnread ? 'fw-bold' : ''}">${notification.title}</h6>
                                        <p class="mb-1 text-muted small">${notification.message}</p>
                                        <small class="text-muted">${timeAgo}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-${getNotificationBadgeColor(notification.type)}">${notification.type}</span>
                                        ${isUnread ? '<span class="badge bg-danger ms-1">New</span>' : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    content.innerHTML = html;
                } else {
                    content.innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No notifications</h5>
                            <p class="text-muted">You don't have any notifications yet.</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                content.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5 class="text-warning">Error loading notifications</h5>
                        <p class="text-muted">Please try again later.</p>
                    </div>
                `;
            });
    }

    function getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
        return `${Math.floor(diffInSeconds / 86400)}d ago`;
    }

    function getNotificationBadgeColor(type) {
        const colors = {
            'success': 'success',
            'warning': 'warning',
            'error': 'danger',
            'info': 'info'
        };
        return colors[type] || 'secondary';
    }

    function updateUploadNotificationBadge() {
        fetch('/api/notifications')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.unread > 0) {
                    const badge = document.getElementById('uploadNotificationBadge');
                    if (badge) {
                        badge.textContent = data.unread;
                        badge.style.display = 'inline-block';
                    }
                } else {
                    const badge = document.getElementById('uploadNotificationBadge');
                    if (badge) {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.log('Failed to fetch notifications:', error);
            });
    }

    function updateUploadStatus() {
        fetch('/api-status-public')
            .then(response => response.json())
            .then(data => {
                const statusAlert = document.getElementById('uploadStatusAlert');
                const statusTitle = document.getElementById('uploadStatusTitle');
                const statusMessage = document.getElementById('uploadStatusMessage');
                const statusTime = document.getElementById('uploadStatusTime');
                const statusSpinner = document.getElementById('uploadStatusSpinner');

                if (data.success && data.status === 'success') {
                    statusAlert.className = 'alert alert-success d-flex align-items-center mb-4';
                    statusTitle.textContent = 'System Ready';
                    statusMessage.textContent = 'API connected and ready to process uploads';
                    statusSpinner.style.display = 'none';
                } else {
                    statusAlert.className = 'alert alert-warning d-flex align-items-center mb-4';
                    statusTitle.textContent = 'System Warning';
                    statusMessage.textContent = data.message || 'API connection issues detected';
                    statusSpinner.style.display = 'none';
                }

                statusTime.textContent = new Date().toLocaleTimeString();
            })
            .catch(error => {
                const statusAlert = document.getElementById('uploadStatusAlert');
                const statusTitle = document.getElementById('uploadStatusTitle');
                const statusMessage = document.getElementById('uploadStatusMessage');
                const statusTime = document.getElementById('uploadStatusTime');
                const statusSpinner = document.getElementById('uploadStatusSpinner');

                statusAlert.className = 'alert alert-danger d-flex align-items-center mb-4';
                statusTitle.textContent = 'System Error';
                statusMessage.textContent = 'Unable to verify system status';
                statusSpinner.style.display = 'none';
                statusTime.textContent = new Date().toLocaleTimeString();
            });
    }

    // Enhanced upload with proper token vending
    function handleUpload(e) {
        e.preventDefault();
        
        const fileInput = document.getElementById('csvFile');
        if (!fileInput.files.length) {
            utils.showAlert('danger', 'Please select a CSV file to upload');
            return;
        }
        
        const file = fileInput.files[0];
        const formData = new FormData(e.target);
        
        const uploadProgress = document.getElementById('uploadProgress');
        const uploadResults = document.getElementById('uploadResults');
        
        // Show progress
        uploadProgress.classList.remove('d-none');
        document.getElementById('uploadBtn').disabled = true;
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value || 
                         window.csrfToken;
        
        fetch('/bulk-token/upload', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Upload response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Upload response data:', data);
            uploadProgress.classList.add('d-none');
            document.getElementById('uploadBtn').disabled = false;
            
            if (data.success) {
                // Show detailed success message
                const message = `CSV uploaded successfully! ${data.valid_rows} valid recipients found${data.skipped_rows > 0 ? `, ${data.skipped_rows} rows skipped` : ''}.`;
                utils.showAlert('success', message);
                
                // Update notification badge
                updateUploadNotificationBadge();
                
                // Show batch processing options
                if (data.batch_id) {
                    currentBatchId = data.batch_id;
                    console.log('Showing batch processing options for batch:', data.batch_id);
                    showBatchProcessingOptions(data.batch_id);
                } else {
                    console.warn('No batch_id in response:', data);
                }
            } else {
                console.error('Upload failed:', data);
                utils.showAlert('danger', data.message || 'Upload failed');
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            uploadProgress.classList.add('d-none');
            document.getElementById('uploadBtn').disabled = false;
            utils.showAlert('danger', 'Upload failed: ' + error.message);
        });
    }

    // Show batch processing options
    function showBatchProcessingOptions(batchId) {
        console.log('Creating batch processing options for batch:', batchId);
        
        // Remove any existing batch options
        const existingOptions = document.querySelector('.batch-processing-options');
        if (existingOptions) {
            existingOptions.remove();
        }
        
        const message = `
            <div class="alert alert-info batch-processing-options">
                <h6><i class="fas fa-info-circle"></i> Batch Ready for Processing</h6>
                <p class="mb-2">Your CSV has been uploaded and validated successfully!</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm" onclick="startProcessing(${batchId})">
                        <i class="fas fa-play"></i> Start Token Distribution
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.location.href='/bulk-token/history'">
                        <i class="fas fa-history"></i> View Batch History
                    </button>
                </div>
            </div>
        `;
        
        // Insert the message after the upload form
        const form = document.getElementById('uploadForm');
        if (form) {
            const alertDiv = document.createElement('div');
            alertDiv.innerHTML = message;
            form.parentNode.insertBefore(alertDiv.firstChild, form.nextSibling);
            console.log('Batch processing options displayed');
        } else {
            console.error('Upload form not found');
        }
    }

    // Start processing function
    function startProcessing(batchId = null) {
        const batch = batchId || currentBatchId;
        console.log('Starting processing for batch:', batch);
        
        if (!batch) {
            utils.showAlert('danger', 'No batch selected for processing');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;

        console.log('CSRF Token:', csrfToken ? 'Found' : 'Not found');

        fetch(`/bulk-token/process/${batch}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('Process response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Process response data:', data);
            if (data.success) {
                utils.showAlert('success', data.message);
                // Start polling for status updates
                pollProcessingStatus();
            } else {
                utils.showAlert('danger', data.message || 'Failed to start processing');
            }
        })
        .catch(error => {
            console.error('Processing error:', error);
            utils.showAlert('danger', 'Failed to start processing: ' + error.message);
        });
    }

    // Initialize notification polling
    setInterval(() => {
        updateUploadNotificationBadge();
        updateUploadStatus();
    }, 30000);

    // Initial load
    updateUploadNotificationBadge();
    updateUploadStatus();
</script>
@endsection