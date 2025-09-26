@extends('layouts.app')

@section('title', 'Bulk Token Dashboard')

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
                        <li><strong>phone_number</strong> - Nigerian phone number (e.g., 08012345678)</li>
                        <li><strong>disco</strong> - Distribution company code (e.g., EKEDC, IKEDC, AEDC)</li>
                        <li><strong>meter_number</strong> - Electricity meter number (10-15 digits)</li>
                        <li><strong>meter_type</strong> - Meter type (prepaid or postpaid)</li>
                        <li><strong>amount</strong> - Token amount in Naira (e.g., 1000.00)</li>
                        <li><strong>customer_name</strong> - <em>Optional:</em> Customer name if different from recipient</li>
                    </ul>
                    <small class="text-muted">
                        <a href="#" onclick="downloadSampleCSV()">
                            <i class="fas fa-download"></i> Download Sample CSV
                        </a>
                    </small>
                </div>

                <!-- Upload Form -->
                <form id="uploadForm" enctype="multipart/form-data">
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
@endsection

@section('scripts')
<script>
    let currentBatchId = null;
    
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
            fileName.textContent = file.name;
            fileSize.textContent = `(${formatFileSize(file.size)})`;
            fileInfo.classList.remove('d-none');
            uploadBtn.disabled = false;
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
        
        fetch('{{ route("bulk-token.upload") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': window.csrfToken
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
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
    
    function downloadSampleCSV() {
        const csvContent = "name,address,phone_number,disco,meter_number,meter_type,amount,customer_name\n" +
                          "John Doe,123 Main St Lagos,08012345678,EKEDC,12345678901,prepaid,1000.00,\n" +
                          "Jane Smith,456 Victoria Island Lagos,08098765432,IKEDC,98765432109,prepaid,1500.50,Jane Smith\n" +
                          "Mike Johnson,789 Ikeja Lagos,07011223344,AEDC,11122334455,postpaid,2000.00,";
        
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'sample_recipients.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
</script>
@endsection