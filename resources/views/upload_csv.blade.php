@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-upload me-2"></i>Upload CSV</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('upload_csv.process') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="csv_file" class="form-label fw-bold">
                                <i class="fas fa-file-csv me-2"></i>Select CSV File
                            </label>
                            <input 
                                type="file" 
                                name="csv_file" 
                                id="csv_file" 
                                class="form-control @error('csv_file') is-invalid @enderror" 
                                accept=".csv" 
                                required>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Only CSV files are accepted. Maximum file size: 2MB.
                            </small>
                        </div>

                        <div class="mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>CSV Format Requirements:</h6>
                                    <ul class="mb-0">
                                        <li>First row must contain column headers</li>
                                        <li>Required columns: <code>meter_number</code>, <code>amount</code>, <code>disco</code></li>
                                        <li>Optional columns: <code>customer_name</code>, <code>phone</code></li>
                                        <li>File encoding: UTF-8</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-cloud-upload-alt me-2"></i>Upload CSV
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sample CSV Template -->
            <div class="card shadow mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-download me-2"></i>Download Sample CSV Template</h5>
                </div>
                <div class="card-body">
                    <p>Download a sample CSV template to ensure your file is properly formatted:</p>
                    <a href="{{ route('upload_csv.download_template') }}" class="btn btn-success">
                        <i class="fas fa-file-download me-2"></i>Download Template
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
});

// File validation
document.getElementById('csv_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('File size exceeds 2MB. Please select a smaller file.');
            e.target.value = '';
        }
        if (!file.name.endsWith('.csv')) {
            alert('Please select a CSV file.');
            e.target.value = '';
        }
    }
});
</script>
@endpush
@endsection
