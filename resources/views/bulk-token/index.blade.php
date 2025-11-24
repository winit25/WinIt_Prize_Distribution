@extends('layouts.sidebar')

@section('title', 'Bulk Token Dashboard')

@push('styles')
<style>
    .upload-area {
        border: 2px dashed var(--winit-border);
        border-radius: 1rem;
        padding: 3rem 2rem;
        text-align: center;
        background: linear-gradient(135deg, rgba(1, 1, 51, 0.03) 0%, var(--winit-light) 100%);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .upload-area:hover {
        border-color: var(--winit-accent);
        background: linear-gradient(135deg, rgba(23, 247, 182, 0.1) 0%, rgba(23, 247, 182, 0.05) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(23, 247, 182, 0.3);
    }

    .upload-area.dragover {
        border-color: var(--winit-accent);
        background: linear-gradient(135deg, rgba(23, 247, 182, 0.15) 0%, rgba(23, 247, 182, 0.08) 100%);
        transform: scale(1.02);
        box-shadow: 0 6px 16px rgba(23, 247, 182, 0.4);
    }

    .metric-card {
        background: linear-gradient(135deg, #010133 0%, #01011b 100%);
        border-radius: 1rem;
        color: white;
        text-align: center;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(1, 1, 51, 0.3);
    }

    .metric-card:hover {
        background: linear-gradient(135deg, #010133 0%, var(--winit-accent) 100%);
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(23, 247, 182, 0.4);
    }

    .form-control {
        border: 2px solid var(--winit-border);
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        font-family: 'Montserrat', sans-serif;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--winit-accent);
        box-shadow: 0 0 0 0.2rem rgba(23, 247, 182, 0.25);
    }

    .alert {
        border-radius: 1rem;
        border: none;
        font-family: 'Montserrat', sans-serif;
    }

    .alert-info {
        background: linear-gradient(135deg, rgba(1, 1, 51, 0.08) 0%, rgba(1, 1, 51, 0.03) 100%);
        color: #010133;
        border: 1px solid rgba(1, 1, 51, 0.2);
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
        background: linear-gradient(135deg, #010133 0%, #01011b 100%);
        border-radius: 0.5rem;
    }

    .btn-outline-primary {
        border: 2px solid #010133;
        color: #010133;
        background: transparent;
        border-radius: 0.75rem;
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background: var(--winit-accent);
        border-color: var(--winit-accent);
        color: #010133;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(23, 247, 182, 0.5);
    }

    .notification-item {
        transition: all 0.3s ease;
        border-left: 4px solid #010133;
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
        background: linear-gradient(135deg, #010133 0%, #01011b 100%);
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

    .btn-primary {
        background: linear-gradient(135deg, #010133 0%, #01011b 100%);
        border: none;
        box-shadow: 0 4px 12px rgba(1, 1, 51, 0.3);
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--winit-accent) 0%, #13d9a0 100%);
        color: #010133;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(23, 247, 182, 0.5);
    }

    .btn-success {
        background: linear-gradient(135deg, #010133 0%, #01011b 100%);
        border: none;
        box-shadow: 0 4px 12px rgba(1, 1, 51, 0.3);
        transition: all 0.3s ease;
    }

    .btn-success:hover {
        background: linear-gradient(135deg, var(--winit-accent) 0%, #13d9a0 100%);
        color: #010133;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(23, 247, 182, 0.5);
    }

    .card {
        border: none;
        border-radius: 1rem;
    }

    .card-header {
        border-radius: 1rem 1rem 0 0 !important;
    }

    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 0.5rem;
        font-weight: 600;
    }

    .status-uploaded {
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
    }

    .status-processing {
        background: rgba(249, 115, 22, 0.1);
        color: #ea580c;
    }

    .status-completed {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
    }

    .status-failed {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
    }

    /* Toggle Switch Styling */
    .form-check-input:checked {
        background-color: #10b981;
        border-color: #10b981;
    }

    .form-check-input:focus {
        border-color: rgba(18, 18, 104, 0.3);
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(18, 18, 104, 0.25);
    }

    /* Disabled template card styling */
    .template-disabled {
        opacity: 0.5;
        pointer-events: none;
    }

    /* Enhanced upload area */
    .upload-area {
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Template cards hover effect */
    #smsTemplateCard:hover:not(.template-disabled),
    #emailTemplateCard:hover:not(.template-disabled) {
        box-shadow: 0 4px 12px rgba(18, 18, 104, 0.15);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }

    /* Notification toggle cards */
    .notification-toggle-card {
        transition: all 0.3s ease;
    }

    .notification-toggle-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@section('content')
<!-- API Integration Update Banner -->
<div class="alert alert-success mb-4" style="border-radius: 1rem; border: 2px solid #10b981; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);">
    <div class="d-flex align-items-start">
        <div class="flex-shrink-0">
            <i class="fas fa-check-circle fa-2x" style="color: #10b981;"></i>
        </div>
        <div class="ms-3 flex-grow-1">
            <h5 class="alert-heading mb-2" style="color: #059669; font-weight: 700;">
                <i class="fas fa-bolt me-2"></i>BuyPower API Integration Updated
            </h5>
            <p class="mb-2" style="color: #065f46;">
                The system now uses the optimized <strong>/vend</strong> endpoint which combines order creation and token vending in a single API call for faster processing.
            </p>
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-check-circle me-2" style="color: #10b981;"></i>
                        <span><strong>Response Time:</strong> ~4-5 seconds per transaction</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-check-circle me-2" style="color: #10b981;"></i>
                        <span><strong>Success Rate:</strong> Improved reliability</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-check-circle me-2" style="color: #10b981;"></i>
                        <span><strong>Verified:</strong> Working with test meter 12345678910 (EKO)</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-info-circle me-2" style="color: #0891b2;"></i>
                        <span><strong>Sample CSV:</strong> Now includes verified test data</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Upload Section -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #010133 0%, #01011b 100%);">
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
                    <div class="alert alert-success mt-3" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%); border: 1px solid #10b981;">
                        <h6><i class="fas fa-check-circle me-2" style="color: #10b981;"></i>Verified Test Data (Included in Sample CSV):</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="mb-0">
                                    <li><strong>Phone:</strong> 08000000000 ✅ (Working)</li>
                                    <li><strong>Phone:</strong> 08012345678 ✅ (Verified)</li>
                                    <li><strong>Meter:</strong> 12345678910 ✅ (Verified)</li>
                                    <li><strong>Disco:</strong> EKO ✅ (Verified)</li>
                                    <li><strong>Disco:</strong> IKEJA ✅ (Verified)</li>
                                    <li><strong>Disco:</strong> ABUJA ✅ (Verified)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="mb-0">
                                    <li><strong>Amount:</strong> ₦1,000.00 ✅ (Minimum tested)</li>
                                    <li><strong>Amount:</strong> ₦1,500.00 ✅ (Tested)</li>
                                    <li><strong>Amount:</strong> ₦2,000.00 ✅ (Tested)</li>
                                    <li><strong>Meter Type:</strong> prepaid ✅</li>
                                    <li><strong>Status:</strong> Successfully tested ✅</li>
                                    <li><strong>Response Time:</strong> ~4.5 seconds</li>
                                </ul>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-lightbulb me-1"></i>The sample CSV file includes verified test data that works with BuyPower sandbox API. Each row has a unique phone number (required for batch uploads) and uses tested meter numbers (12345678910) that have been successfully validated.
                        </small>
                    </div>
                    <div class="alert alert-warning mt-3">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Important Notes:</h6>
                        <ul class="mb-0">
                            <li><strong>Phone Number:</strong> Must be 11 digits starting with 080, 081, 070, 090, or 091</li>
                            <li><strong>Meter Number:</strong> Must be exactly 11 digits and valid for the specified disco</li>
                            <li><strong>Disco Codes:</strong> Use the exact codes listed above (not EKEDC, IKEDC, etc.)</li>
                            <li><strong>Amount:</strong> Minimum ₦500, recommended ₦1,000 for testing</li>
                            <li><strong>Rate Limiting:</strong> Avoid multiple rapid requests to the same meter (wait 20 seconds between attempts)</li>
                        </ul>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('bulk-token.download-sample') }}" 
                           class="btn btn-outline-primary btn-sm" 
                           download="sample_recipients.csv"
                           data-bs-toggle="tooltip" 
                           data-bs-placement="top" 
                           title="Download sample CSV with verified test data that works with BuyPower sandbox. Includes: Phone: 08000000000, Meter: 12345678910, Discos: EKO, IKEJA, ABUJA">
                            <i class="fas fa-download me-2"></i> Download Sample CSV
                            <span class="badge bg-success ms-2">✅ Verified Test Data</span>
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
                        <div id="uploadStatusBalance" class="small mt-1" style="display: none;">
                            <i class="fas fa-wallet me-1"></i>Account Balance: <strong id="balanceAmount">Loading...</strong>
                        </div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted" id="uploadStatusTime">--:--</small>
                        </div>
                    </div>
                    
                <!-- Password Protection Modal -->
                @if(!$passwordVerified)
                <div class="modal fade show" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="false" style="display: block; background: rgba(0,0,0,0.8);" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="border-radius: 1rem; border: 2px solid var(--winit-accent);">
                            <div class="modal-header" style="background: linear-gradient(135deg, #010133 0%, #01011b 100%); border-radius: 1rem 1rem 0 0; border-bottom: 2px solid var(--winit-accent);">
                                <h5 class="modal-title text-white" id="passwordModalLabel">
                                    <i class="fas fa-lock me-2"></i>Secure Access Required
                                </h5>
                            </div>
                            <div class="modal-body" style="padding: 2rem; background: var(--winit-light);">
                                <div class="text-center mb-4">
                                    <img src="{{ asset('images/winit-logo.png') }}" alt="WinIt Logo" style="width: 120px; height: auto; margin-bottom: 1rem;">
                                    <h6 class="mb-2" style="color: rgb(18, 18, 104);">CSV Upload Access</h6>
                                    <p class="text-muted small">Please enter your account password to access the CSV upload page.</p>
                                </div>
                                
                                <form id="passwordForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="uploadPassword" class="form-label">
                                            <i class="fas fa-key me-1"></i>Password
                                        </label>
                                        <input type="password" 
                                               class="form-control form-control-lg" 
                                               id="uploadPassword" 
                                               name="password" 
                                               placeholder="Enter password"
                                               required
                                               autofocus
                                               style="border-radius: 0.75rem; border: 2px solid var(--winit-border); padding: 0.75rem 1rem;">
                                        <div id="passwordError" class="text-danger small mt-2" style="display: none;"></div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg" id="verifyPasswordBtn" style="border-radius: 0.75rem; background: linear-gradient(135deg, rgb(18, 18, 104) 0%, var(--winit-accent) 100%); border: none;">
                                            <i class="fas fa-unlock me-2"></i>Verify & Access
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt me-1"></i>Enter your account password for additional security
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Hide main content until password is verified -->
                <div id="mainContent" style="display: none;">
                @endif

                <!-- Upload Form -->
                @php $canUpload = auth()->check() && auth()->user()->canUploadCsv(); @endphp
                @if(!$canUpload)
                    <div class="alert alert-warning d-flex align-items-start">
                        <i class="fas fa-lock me-2 mt-1"></i>
                        <div>
                            <strong>Read-only access</strong>
                            <div class="small text-muted">You do not have permission to upload CSV files. Please contact an administrator if you believe this is a mistake.</div>
                        </div>
                    </div>
                @endif

                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <!-- Upload Method Toggle -->
                            <div class="btn-group mb-3" role="group" style="width: 100%;">
                                <input type="radio" class="btn-check" name="upload_method" id="uploadMethodFile" value="file" checked>
                                <label class="btn btn-outline-primary" for="uploadMethodFile">
                                    <i class="fas fa-upload me-2"></i>Upload File
                                </label>
                                
                                <input type="radio" class="btn-check" name="upload_method" id="uploadMethodSharePoint" value="sharepoint">
                                <label class="btn btn-outline-primary" for="uploadMethodSharePoint">
                                    <i class="fab fa-microsoft me-2"></i>SharePoint
                                </label>
                            </div>

                            <!-- File Upload Section -->
                            <div id="fileUploadSection">
                                <label class="form-label">CSV File</label>
                                <div class="upload-area" id="uploadArea" @if(!$canUpload) style="pointer-events: none; opacity: 0.5;" @endif>
                                    <input type="file" 
                                           id="csvFile" 
                                           name="csv_file" 
                                           accept=".csv,.txt,text/csv,application/csv"
                                           class="d-none">
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

                            <!-- SharePoint URL Section -->
                            <div id="sharePointSection" style="display: none;">
                                <label class="form-label">SharePoint File URL or Sharing Link</label>
                                <input type="url" 
                                       class="form-control" 
                                       id="sharePointUrl" 
                                       name="sharepoint_url" 
                                       placeholder="https://[tenant].sharepoint.com/... or sharing link"
                                       @if(!$canUpload) disabled @endif>
                                <small class="text-muted mt-1 d-block">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Paste the SharePoint file URL or sharing link here. The system will download and process the file automatically.
                                </small>
                                <div id="sharePointStatus" class="mt-2 d-none">
                                    <small class="text-info">
                                        <i class="fas fa-spinner fa-spin me-1"></i>
                                        <span id="sharePointStatusText">Checking SharePoint file...</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Batch Name (Optional)</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="batch_name" 
                                   placeholder="e.g., Monthly Distribution"
                                   @if(!$canUpload) disabled @endif>
                    </div>
                </div>

                <!-- Notification Settings & Templates -->
                <div class="row mt-4">
                    <div class="col-12">
                        <!-- Notification Toggles -->
                        <div class="card mb-4 shadow-sm" style="border: 1px solid rgba(18, 18, 104, 0.1); border-radius: 1rem;">
                            <div class="card-header" style="background: linear-gradient(135deg, rgba(18, 18, 104, 0.08) 0%, rgba(18, 18, 104, 0.03) 100%); border-bottom: 1px solid rgba(18, 18, 104, 0.1); border-radius: 1rem 1rem 0 0;">
                                <h6 class="mb-0" style="color: rgb(18, 18, 104); font-weight: 600;">
                                    <i class="fas fa-bell me-2"></i>Notification Settings
                                </h6>
                                <small class="text-muted">Enable or disable SMS and Email notifications for this batch</small>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center p-3" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(59, 130, 246, 0.02) 100%); border-radius: 0.75rem; border: 2px solid rgba(59, 130, 246, 0.2);">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-comment-sms fa-2x me-3" style="color: #3b82f6;"></i>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">SMS Notifications</h6>
                                                    <small class="text-muted">Send token via SMS</small>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enableSmsToggle" name="enable_sms" value="1" checked style="width: 3rem; height: 1.5rem; cursor: pointer;" onchange="handleSmsToggle(this)">
                                                <label class="form-check-label" for="enableSmsToggle" style="display: none;"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center p-3" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(16, 185, 129, 0.02) 100%); border-radius: 0.75rem; border: 2px solid rgba(16, 185, 129, 0.2);">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-envelope fa-2x me-3" style="color: #10b981;"></i>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">Email Notifications</h6>
                                                    <small class="text-muted">Send token via Email</small>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enableEmailToggle" name="enable_email" value="1" checked style="width: 3rem; height: 1.5rem; cursor: pointer;" onchange="handleEmailToggle(this)">
                                                <label class="form-check-label" for="enableEmailToggle" style="display: none;"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SMS Template Card -->
                        <div class="card mb-3" id="smsTemplateCard" style="border: 1px solid rgba(18, 18, 104, 0.1); border-radius: 1rem; transition: all 0.3s ease; display: block;">
                            <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(59, 130, 246, 0.02) 100%); border-bottom: 1px solid rgba(18, 18, 104, 0.1); border-radius: 1rem 1rem 0 0;">
                                <div>
                                    <h6 class="mb-0" style="color: rgb(18, 18, 104); font-weight: 600;">
                                        <i class="fas fa-comment-sms me-2"></i>SMS Message Template
                                    </h6>
                                    <small class="text-muted">Customize the SMS message sent to recipients</small>
                                </div>
                                <span class="badge bg-info" id="smsStatusBadge">Enabled</span>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-info-circle me-1"></i>Available Variables:
                                    </label>
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <span class="badge bg-info" style="cursor: pointer;" onclick="insertVariable('sms_template', '{name}')">{name}</span>
                                        <span class="badge bg-info" style="cursor: pointer;" onclick="insertVariable('sms_template', '{customer_name}')">{customer_name}</span>
                                        <span class="badge bg-info" style="cursor: pointer;" onclick="insertVariable('sms_template', '{token}')">{token}</span>
                                        <span class="badge bg-info" style="cursor: pointer;" onclick="insertVariable('sms_template', '{amount}')">{amount}</span>
                                        <span class="badge bg-info" style="cursor: pointer;" onclick="insertVariable('sms_template', '{units}')">{units}</span>
                                        <span class="badge bg-info" style="cursor: pointer;" onclick="insertVariable('sms_template', '{disco}')">{disco}</span>
                                        <span class="badge bg-info" style="cursor: pointer;" onclick="insertVariable('sms_template', '{meter_number}')">{meter_number}</span>
                                        <span class="badge bg-info" style="cursor: pointer;" onclick="insertVariable('sms_template', '{date}')">{date}</span>
                                        <span class="badge bg-info" style="cursor: pointer;" onclick="insertVariable('sms_template', '{time}')">{time}</span>
                                        <span class="badge bg-info" style="cursor: pointer;" onclick="insertVariable('sms_template', '{year}')">{year}</span>
                                        <span class="badge bg-info" style="cursor: pointer;" onclick="insertVariable('sms_template', '{month}')" title="Full month name (e.g., January)">{month}</span>
                                        <span class="badge bg-info" style="cursor: pointer;" onclick="insertVariable('sms_template', '{month_short}')" title="Short month name (e.g., Jan)">{month_short}</span>
                                        <span class="badge bg-info" style="cursor: pointer;" onclick="insertVariable('sms_template', '{month_number}')" title="Month number with zero (e.g., 01)">{month_number}</span>
                                        <span class="badge bg-info" style="cursor: pointer;" onclick="insertVariable('sms_template', '{month_numeric}')" title="Month number (e.g., 1)">{month_numeric}</span>
                                    </div>
                                </div>
                                <textarea 
                                    id="sms_template" 
                                    name="sms_template" 
                                    class="form-control" 
                                    rows="5" 
                                    placeholder="Your electricity WinIt Prize Distribution token is: {token}
Amount: ₦{amount}
Disco: {disco}
Meter: {meter_number}
Units: {units} KWh
Date: {date} {time}
Thank you for using WinIt Prize Distribution!">
Your electricity WinIt Prize Distribution token is: {token}
Amount: ₦{amount}
Disco: {disco}
Meter: {meter_number}
Units: {units} KWh
Date: {date} {time}
Thank you for using WinIt Prize Distribution!</textarea>
                                <small class="text-muted mt-2 d-block">
                                    <i class="fas fa-lightbulb me-1"></i>Click on variables above to insert them into your message
                                </small>
                            </div>
                        </div>

                        <!-- Email Template Card -->
                        <div class="card mb-3" id="emailTemplateCard" style="border: 1px solid rgba(18, 18, 104, 0.1); border-radius: 1rem; transition: all 0.3s ease; display: block;">
                            <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(16, 185, 129, 0.02) 100%); border-bottom: 1px solid rgba(18, 18, 104, 0.1); border-radius: 1rem 1rem 0 0;">
                                <div>
                                    <h6 class="mb-0" style="color: rgb(18, 18, 104); font-weight: 600;">
                                        <i class="fas fa-envelope me-2"></i>Email Message Template
                                    </h6>
                                    <small class="text-muted">Customize the email message sent to recipients</small>
                                </div>
                                <span class="badge bg-success" id="emailStatusBadge">Enabled</span>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-info-circle me-1"></i>Available Variables:
                                    </label>
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <span class="badge bg-success" style="cursor: pointer;" onclick="insertVariable('email_template', '{name}')">{name}</span>
                                        <span class="badge bg-success" style="cursor: pointer;" onclick="insertVariable('email_template', '{customer_name}')">{customer_name}</span>
                                        <span class="badge bg-success" style="cursor: pointer;" onclick="insertVariable('email_template', '{token}')">{token}</span>
                                        <span class="badge bg-success" style="cursor: pointer;" onclick="insertVariable('email_template', '{amount}')">{amount}</span>
                                        <span class="badge bg-success" style="cursor: pointer;" onclick="insertVariable('email_template', '{units}')">{units}</span>
                                        <span class="badge bg-success" style="cursor: pointer;" onclick="insertVariable('email_template', '{disco}')">{disco}</span>
                                        <span class="badge bg-success" style="cursor: pointer;" onclick="insertVariable('email_template', '{meter_number}')">{meter_number}</span>
                                        <span class="badge bg-success" style="cursor: pointer;" onclick="insertVariable('email_template', '{address}')">{address}</span>
                                        <span class="badge bg-success" style="cursor: pointer;" onclick="insertVariable('email_template', '{date}')">{date}</span>
                                        <span class="badge bg-success" style="cursor: pointer;" onclick="insertVariable('email_template', '{time}')">{time}</span>
                                        <span class="badge bg-success" style="cursor: pointer;" onclick="insertVariable('email_template', '{year}')">{year}</span>
                                        <span class="badge bg-success" style="cursor: pointer;" onclick="insertVariable('email_template', '{month}')" title="Full month name (e.g., January)">{month}</span>
                                        <span class="badge bg-success" style="cursor: pointer;" onclick="insertVariable('email_template', '{month_short}')" title="Short month name (e.g., Jan)">{month_short}</span>
                                        <span class="badge bg-success" style="cursor: pointer;" onclick="insertVariable('email_template', '{month_number}')" title="Month number with zero (e.g., 01)">{month_number}</span>
                                        <span class="badge bg-success" style="cursor: pointer;" onclick="insertVariable('email_template', '{month_numeric}')" title="Month number (e.g., 1)">{month_numeric}</span>
                                    </div>
                                </div>
                                <textarea 
                                    id="email_template" 
                                    name="email_template" 
                                    class="form-control" 
                                    rows="8" 
                                    placeholder="Dear {name},

Your electricity token from WinIt Prize Distribution is ready!

Token: {token}
Amount: ₦{amount}
Units: {units} KWh
Disco: {disco}
Meter Number: {meter_number}
Date: {date} {time}

Thank you for using WinIt Prize Distribution!">
Dear {name},

Your electricity token from WinIt Prize Distribution is ready!

Token: {token}
Amount: ₦{amount}
Units: {units} KWh
Disco: {disco}
Meter Number: {meter_number}
Date: {date} {time}

Thank you for using WinIt Prize Distribution!</textarea>
                                <small class="text-muted mt-2 d-block">
                                    <i class="fas fa-lightbulb me-1"></i>Click on variables above to insert them into your message. HTML is supported for email templates.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                    <button type="submit" class="btn btn-primary btn-lg" id="uploadBtn" @if(!$canUpload) disabled @endif>
                        <i class="fas fa-upload"></i>
                        Upload & Parse CSV
                    </button>
            </form>

            <!-- Upload Progress -->
                <div id="uploadProgress" class="mt-4 d-none">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h5 class="mb-2"><i class="fas fa-cloud-upload-alt"></i> Uploading CSV File...</h5>
                            <p class="text-muted mb-3">Please wait while we process your file</p>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                     role="progressbar" style="width: 100%"></div>
                            </div>
                            <small class="text-muted mt-2 d-block">This may take a few moments depending on file size</small>
                        </div>
                    </div>
            </div>

            <!-- Upload Results -->
                <div id="uploadResults" class="mt-4 d-none">
                <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle"></i> Upload Successful!</h6>
                        <p class="mb-2">Your CSV has been processed:</p>
                        <ul class="mb-0" id="uploadResultsList">
                            <li><strong id="totalRecipients"></strong> recipients found</li>
                            <li><strong id="totalAmount"></strong> total amount</li>
                        </ul>
                        <div id="skippedRowsInfo" class="mt-2" style="display: none;">
                            <small class="text-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span id="skippedRowsCount"></span> rows skipped due to validation errors
                            </small>
                    </div>
                </div>
                
                    <button type="button" class="btn btn-success btn-lg" id="processBtn" onclick="startProcessing()">
                    <i class="fas fa-play"></i>
                    Start Token Distribution
                </button>
                    <a href="{{ route('bulk-token.history') }}" class="btn btn-outline-secondary btn-lg ms-2">
                        <i class="fas fa-history"></i>
                        View Batch History
                    </a>
            </div>

            <!-- Processing Status -->
                <div id="processingStatus" class="mt-4 d-none">
                    <div class="card bg-light">
                    <div class="card-body">
                        <h6><i class="fas fa-cog fa-spin"></i> Processing Batch Synchronously...</h6>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-info-circle"></i> Processing recipients in batches (each batch completes before next starts)
                        </small>
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
        <div class="card shadow-sm">
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
                                    <div class="progress mt-2" style="height: 5px; background: rgba(1, 1, 51, 0.1);">
                                        <div class="progress-bar" 
                                             style="width: {{ $batch->completion_percentage }}%; background: {{ $batch->status == 'completed' ? 'linear-gradient(135deg, var(--winit-accent) 0%, #13d9a0 100%)' : 'linear-gradient(135deg, #010133 0%, #01011b 100%)' }};"></div>
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
        <div class="card mt-4 shadow-sm">
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
                        <div class="card text-white mb-2" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);">
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

@if(!$passwordVerified)
</div> <!-- End mainContent -->
@endif

@endsection

@push('scripts')
<script>
    // Password Verification Handler
    @if(!$passwordVerified)
    document.addEventListener('DOMContentLoaded', function() {
        const passwordForm = document.getElementById('passwordForm');
        const passwordInput = document.getElementById('uploadPassword');
        const passwordError = document.getElementById('passwordError');
        const verifyBtn = document.getElementById('verifyPasswordBtn');
        const passwordModal = document.getElementById('passwordModal');
        const mainContent = document.getElementById('mainContent');

        if (passwordForm) {
            passwordForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const password = passwordInput.value.trim();
                
                if (!password) {
                    passwordError.textContent = 'Please enter a password';
                    passwordError.style.display = 'block';
                    return;
                }

                // Disable button and show loading
                verifyBtn.disabled = true;
                verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying...';
                passwordError.style.display = 'none';

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                     document.querySelector('input[name="_token"]')?.value;

                    console.log('Sending password verification:', {
                        password_length: password.length,
                        password_preview: password.substring(0, 3) + '...'
                    });

                    const verifyUrl = '{{ route("bulk-token.verify-password") }}';
                    console.log('Verifying password at:', verifyUrl);
                    
                    const response = await fetch(verifyUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ password: password })
                    });

                    console.log('Response status:', response.status, response.statusText);
                    
                    if (!response.ok) {
                        if (response.status === 405) {
                            throw new Error('Method not allowed. Please refresh the page and try again.');
                        }
                        if (response.status === 419) {
                            throw new Error('CSRF token expired. Please refresh the page and try again.');
                        }
                    }

                    const data = await response.json();

                    if (data.success) {
                        // Hide modal and show content
                        if (passwordModal) {
                            passwordModal.style.display = 'none';
                        }
                        if (mainContent) {
                            mainContent.style.display = 'block';
                        }
                        
                        // Show success message
                        if (typeof utils !== 'undefined' && utils.showAlert) {
                            utils.showAlert('success', '✅ Password verified! Access granted.');
                        }
                        
                        // Clear password field
                        passwordInput.value = '';
                    } else {
                        // Show error
                        passwordError.textContent = data.message || 'Invalid password. Please try again.';
                        passwordError.style.display = 'block';
                        passwordInput.focus();
                        passwordInput.select();
                        
                        // Reset button
                        verifyBtn.disabled = false;
                        verifyBtn.innerHTML = '<i class="fas fa-unlock me-2"></i>Verify & Access';
                    }
                } catch (error) {
                    console.error('Password verification error:', error);
                    passwordError.textContent = 'An error occurred. Please try again.';
                    passwordError.style.display = 'block';
                    
                    // Reset button
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = '<i class="fas fa-unlock me-2"></i>Verify & Access';
                }
            });

            // Allow Enter key to submit
            if (passwordInput) {
                passwordInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        passwordForm.dispatchEvent(new Event('submit'));
                    }
                });
            }
        }
    });
    @else
    // Password already verified, show content immediately
    document.addEventListener('DOMContentLoaded', function() {
        const mainContent = document.getElementById('mainContent');
        if (mainContent) {
            mainContent.style.display = 'block';
        }
    });
    @endif

    let currentBatchId = null;
    
    // Notification Queue System
    const notificationQueue = {
        queue: [],
        isShowing: false,
        container: null,
        
        init: function() {
            // Create notification container if it doesn't exist
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.id = 'notificationContainer';
                this.container.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    max-width: 500px;
                    width: 100%;
                `;
                document.body.appendChild(this.container);
            }
        },
        
        add: function(type, message, duration = 10000) {
            this.queue.push({ type, message, duration });
            if (!this.isShowing) {
                this.showNext();
            }
        },
        
        showNext: function() {
            if (this.queue.length === 0) {
                this.isShowing = false;
                return;
            }
            
            this.isShowing = true;
            const notification = this.queue.shift();
            this.display(notification.type, notification.message, notification.duration);
        },
        
        display: function(type, message, duration) {
            this.init();
            
            // Map alert types to system design classes
            const alertClassMap = {
                'success': 'alert-success',
                'danger': 'alert-danger',
                'warning': 'alert-warning',
                'info': 'alert-info'
            };
            
            const alertClass = alertClassMap[type] || 'alert-info';
            
            // Create notification element matching system design
            const notificationDiv = document.createElement('div');
            notificationDiv.className = `alert ${alertClass} alert-dismissible fade show mb-3`;
            notificationDiv.style.cssText = `
                border-radius: 1rem;
                border: none;
                font-family: 'Montserrat', sans-serif;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideInRight 0.3s ease-out;
                min-width: 400px;
            `;
            
            // Add icon based on type
            const iconMap = {
                'success': 'fa-check-circle',
                'danger': 'fa-exclamation-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            };
            const icon = iconMap[type] || 'fa-info-circle';
            
            notificationDiv.innerHTML = `
                <div class="d-flex align-items-start">
                    <i class="fas ${icon} me-3 mt-1" style="font-size: 1.25rem;"></i>
                    <div class="flex-grow-1">
                        ${message}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="notificationQueue.dismiss()"></button>
                </div>
            `;
            
            // Add slide-in animation CSS if not already added
            if (!document.getElementById('notification-animations')) {
                const style = document.createElement('style');
                style.id = 'notification-animations';
                style.textContent = `
                    @keyframes slideInRight {
                        from {
                            transform: translateX(100%);
                            opacity: 0;
                        }
                        to {
                            transform: translateX(0);
                            opacity: 1;
                        }
                    }
                    @keyframes slideOutRight {
                        from {
                            transform: translateX(0);
                            opacity: 1;
                        }
                        to {
                            transform: translateX(100%);
                            opacity: 0;
                        }
                    }
                `;
                document.head.appendChild(style);
            }
            
            this.container.appendChild(notificationDiv);
            
            // Auto-dismiss after duration
            setTimeout(() => {
                this.dismiss();
            }, duration);
        },
        
        dismiss: function() {
            const notifications = this.container.querySelectorAll('.alert');
            if (notifications.length > 0) {
                const currentNotification = notifications[0];
                currentNotification.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => {
                    if (currentNotification.parentNode) {
                        currentNotification.remove();
                    }
                    // Show next notification in queue
                    setTimeout(() => this.showNext(), 300);
                }, 300);
            } else {
                this.showNext();
            }
        }
    };

    // Utility functions
    const utils = {
        showAlert: function(type, message, duration = 10000) {
            notificationQueue.add(type, message, duration);
        },
        formatCurrency: function(amount) {
            return '₦' + parseFloat(amount).toLocaleString('en-NG', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },
        async makeRequest(url, options = {}) {
            try {
                const { headers: optionHeaders = {}, credentials, ...restOptions } = options;
                const response = await fetch(url, {
                    credentials: credentials ?? 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        ...optionHeaders,
                    },
                    ...restOptions,
                });
                // ... existing code ...
            } catch (error) {
                // ... existing code ...
            }
        },
    };

    // Insert variable into template textarea
    function insertVariable(textareaId, variable) {
        const textarea = document.getElementById(textareaId);
        if (textarea) {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            const before = text.substring(0, start);
            const after = text.substring(end, text.length);
            
            textarea.value = before + variable + after;
            textarea.selectionStart = textarea.selectionEnd = start + variable.length;
            textarea.focus();
            
            // Update character count for SMS
            if (textareaId === 'sms_template') {
                updateSmsCharCount();
            }
        }
    }

    // Character counter for SMS template
    function updateSmsCharCount() {
        const smsTemplate = document.getElementById('sms_template');
        const charCount = smsTemplate ? smsTemplate.value.length : 0;
        
        // SMS typically has a 160 character limit per message
        const smsLimit = 160;
        const messages = Math.ceil(charCount / smsLimit);
        
        let charCountElement = document.getElementById('smsCharCount');
        if (!charCountElement && smsTemplate) {
            charCountElement = document.createElement('small');
            charCountElement.id = 'smsCharCount';
            charCountElement.className = 'text-muted mt-2 d-block';
            smsTemplate.parentElement.appendChild(charCountElement);
        }
        
        if (charCountElement) {
            if (charCount > 0) {
                charCountElement.innerHTML = `
                    <i class="fas fa-sms me-1"></i>
                    <strong>${charCount}</strong> characters (${messages} SMS ${messages > 1 ? 'messages' : 'message'})
                    ${charCount > smsLimit * 10 ? '<span class="text-danger ms-2">⚠️ Message is very long</span>' : ''}
                `;
            } else {
                charCountElement.innerHTML = '';
            }
        }
    }
    
    // Inline toggle handlers (fallback)
    function handleSmsToggle(toggle) {
        const smsTemplateCard = document.getElementById('smsTemplateCard');
        const smsStatusBadge = document.getElementById('smsStatusBadge');
        const smsTextarea = document.getElementById('sms_template');
        
        if (!smsTemplateCard) {
            console.error('SMS template card not found');
            return;
        }
        
        const isEnabled = toggle.checked;
        console.log('SMS Toggle clicked:', isEnabled);
        
        if (isEnabled) {
            smsTemplateCard.style.display = 'block';
            smsTemplateCard.style.opacity = '1';
            smsTemplateCard.style.pointerEvents = 'auto';
            if (smsStatusBadge) {
                smsStatusBadge.textContent = 'Enabled';
                smsStatusBadge.className = 'badge bg-info';
            }
            if (smsTextarea) {
                smsTextarea.removeAttribute('disabled');
                smsTextarea.removeAttribute('required');
            }
        } else {
            smsTemplateCard.style.display = 'none';
            if (smsTextarea) {
                smsTextarea.removeAttribute('required');
                smsTextarea.value = '';
            }
        }
    }

    function handleEmailToggle(toggle) {
        const emailTemplateCard = document.getElementById('emailTemplateCard');
        const emailStatusBadge = document.getElementById('emailStatusBadge');
        const emailTextarea = document.getElementById('email_template');
        
        if (!emailTemplateCard) {
            console.error('Email template card not found');
            return;
        }
        
        const isEnabled = toggle.checked;
        console.log('Email Toggle clicked:', isEnabled);
        
        if (isEnabled) {
            emailTemplateCard.style.display = 'block';
            emailTemplateCard.style.opacity = '1';
            emailTemplateCard.style.pointerEvents = 'auto';
            if (emailStatusBadge) {
                emailStatusBadge.textContent = 'Enabled';
                emailStatusBadge.className = 'badge bg-success';
            }
            if (emailTextarea) {
                emailTextarea.removeAttribute('disabled');
                emailTextarea.removeAttribute('required');
            }
        } else {
            emailTemplateCard.style.display = 'none';
            if (emailTextarea) {
                emailTextarea.removeAttribute('required');
                emailTextarea.value = '';
            }
        }
    }
    
    // Toggle switch functionality
    function setupNotificationToggles() {
        console.log('Setting up notification toggles...');
        
        const smsToggle = document.getElementById('enableSmsToggle');
        const emailToggle = document.getElementById('enableEmailToggle');
        const smsTemplateCard = document.getElementById('smsTemplateCard');
        const emailTemplateCard = document.getElementById('emailTemplateCard');

        if (!smsToggle || !emailToggle) {
            console.error('Toggles not found');
            return;
        }

        console.log('SMS Toggle:', smsToggle);
        console.log('Email Toggle:', emailToggle);
        console.log('SMS Template Card:', smsTemplateCard);
        console.log('Email Template Card:', emailTemplateCard);

        // Set initial state
        if (smsTemplateCard) {
            handleSmsToggle(smsToggle);
        }
        
        if (emailTemplateCard) {
            handleEmailToggle(emailToggle);
        }

        // Also add event listeners as backup
        smsToggle.addEventListener('change', function() {
            handleSmsToggle(this);
        });

        emailToggle.addEventListener('change', function() {
            handleEmailToggle(this);
        });

        console.log('Toggle setup complete');
    }

    // Upload functionality
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded - Initializing...');
        
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('csvFile');
        const uploadForm = document.getElementById('uploadForm');
        const uploadBtn = document.getElementById('uploadBtn');
        
        // Initialize button state - disable until file is selected
        if (uploadBtn) {
            uploadBtn.disabled = true;
            console.log('Upload button initialized as disabled');
        }
        
        // File upload area interactions
        if (uploadArea && fileInput) {
            uploadArea.addEventListener('click', () => fileInput.click());
            uploadArea.addEventListener('dragover', handleDragOver);
            uploadArea.addEventListener('dragleave', handleDragLeave);
            uploadArea.addEventListener('drop', handleDrop);
            fileInput.addEventListener('change', handleFileSelect);
        }
        
        // Form submission
        if (uploadForm) {
            uploadForm.addEventListener('submit', handleUpload);
            
            // Handle upload method toggle
            const fileMethod = document.getElementById('uploadMethodFile');
            const sharePointMethod = document.getElementById('uploadMethodSharePoint');
            const fileSection = document.getElementById('fileUploadSection');
            const sharePointSection = document.getElementById('sharePointSection');
            const csvFileInput = document.getElementById('csvFile');
            const sharePointUrlInput = document.getElementById('sharePointUrl');
            const uploadBtn = document.getElementById('uploadBtn');

            if (fileMethod && sharePointMethod) {
                fileMethod.addEventListener('change', function() {
                    if (this.checked) {
                        fileSection.style.display = 'block';
                        sharePointSection.style.display = 'none';
                        csvFileInput.removeAttribute('required');
                        sharePointUrlInput.removeAttribute('required');
                        csvFileInput.setAttribute('required', 'required');
                        sharePointUrlInput.value = '';
                        if (uploadBtn) uploadBtn.disabled = true;
                    }
                });

                sharePointMethod.addEventListener('change', function() {
                    if (this.checked) {
                        fileSection.style.display = 'none';
                        sharePointSection.style.display = 'block';
                        csvFileInput.removeAttribute('required');
                        sharePointUrlInput.removeAttribute('required');
                        sharePointUrlInput.setAttribute('required', 'required');
                        csvFileInput.value = '';
                        if (uploadBtn) uploadBtn.disabled = true;
                    }
                });

                // Enable upload button when SharePoint URL is entered
                if (sharePointUrlInput) {
                    sharePointUrlInput.addEventListener('input', function() {
                        if (this.value.trim().length > 0 && sharePointMethod.checked) {
                            if (uploadBtn && !uploadBtn.hasAttribute('data-no-permission')) {
                                uploadBtn.disabled = false;
                            }
                        } else {
                            if (uploadBtn) uploadBtn.disabled = true;
                        }
                    });
                }
            }
        }
        
        // Setup notification toggles - Call immediately
        try {
            setupNotificationToggles();
        } catch (error) {
            console.error('Error setting up notification toggles:', error);
            // Try again after a short delay
            setTimeout(() => {
                try {
                    setupNotificationToggles();
                } catch (retryError) {
                    console.error('Retry failed:', retryError);
                }
            }, 500);
        }
        
        // SMS template character counter
        const smsTemplate = document.getElementById('sms_template');
        if (smsTemplate) {
            smsTemplate.addEventListener('input', updateSmsCharCount);
            updateSmsCharCount(); // Initial count
        }
    });
    
    // Also try to setup toggles when window loads (fallback)
    window.addEventListener('load', function() {
        console.log('Window loaded - Checking toggles...');
        const smsToggle = document.getElementById('enableSmsToggle');
        const emailToggle = document.getElementById('enableEmailToggle');
        if (smsToggle && emailToggle) {
            console.log('Toggles found on window load, setting up...');
            setupNotificationToggles();
        }
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
        const fileInput = document.getElementById('csvFile');
        
        if (files.length > 0) {
            // Use DataTransfer to properly set files on input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(files[0]);
            fileInput.files = dataTransfer.files;
            handleFileSelect();
        }
    }

    function handleFileSelect() {
        const fileInput = document.getElementById('csvFile');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const uploadBtn = document.getElementById('uploadBtn');
        const uploadArea = document.getElementById('uploadArea');
        
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
                if (fileInfo) fileInfo.classList.add('d-none');
                if (uploadBtn) uploadBtn.disabled = true;
                return;
            }
            
            // Validate file size (5MB max)
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                utils.showAlert('danger', 'File size must be less than 5MB');
                fileInput.value = '';
                if (fileInfo) fileInfo.classList.add('d-none');
                if (uploadBtn) uploadBtn.disabled = true;
                return;
            }
            
            // Show file info
            if (fileName) fileName.textContent = file.name;
            if (fileSize) fileSize.textContent = `(${formatFileSize(file.size)})`;
            if (fileInfo) fileInfo.classList.remove('d-none');
            
            // Enable button (check permission first)
            if (uploadBtn && !uploadBtn.hasAttribute('data-no-permission')) {
                uploadBtn.disabled = false;
                console.log('Upload button enabled');
            }
            
            // Visual feedback on upload area
            if (uploadArea) {
                uploadArea.style.borderColor = '#10b981';
                uploadArea.style.background = 'linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(16, 185, 129, 0.02) 100%)';
            }
            
            // Show success message
            utils.showAlert('success', `✅ File "${file.name}" selected successfully. Click "Upload & Parse CSV" to proceed.`);
        } else {
            if (fileInfo) fileInfo.classList.add('d-none');
            if (uploadBtn) uploadBtn.disabled = true;
            // Reset upload area style
            if (uploadArea) {
                uploadArea.style.borderColor = '';
                uploadArea.style.background = '';
            }
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
        
        const uploadMethod = document.querySelector('input[name="upload_method"]:checked')?.value || 'file';
        const fileInput = document.getElementById('csvFile');
        const sharePointUrlInput = document.getElementById('sharePointUrl');
        
        // Validate based on upload method
        if (uploadMethod === 'file') {
            if (!fileInput.files.length) {
                utils.showAlert('danger', 'Please select a CSV file to upload');
                return;
            }
        } else if (uploadMethod === 'sharepoint') {
            if (!sharePointUrlInput.value.trim()) {
                utils.showAlert('danger', 'Please enter a SharePoint file URL or sharing link');
                return;
            }
            
            // Validate URL format
            try {
                new URL(sharePointUrlInput.value);
            } catch (err) {
                utils.showAlert('danger', 'Please enter a valid SharePoint URL');
                return;
            }
        }
        
        const file = fileInput.files[0];
        if (file) {
            console.log('Uploading file:', {
                name: file.name,
                size: file.size,
                type: file.type
            });
        } else {
            console.log('Uploading from SharePoint:', sharePointUrlInput.value);
        }
        
        const formData = new FormData(e.target);
        
        // Remove file input if using SharePoint
        if (uploadMethod === 'sharepoint') {
            formData.delete('csv_file');
        } else {
            formData.delete('sharepoint_url');
        }
        
        const uploadProgress = document.getElementById('uploadProgress');
        const uploadResults = document.getElementById('uploadResults');
        
        // Show progress with better visibility
        uploadProgress.classList.remove('d-none');
        uploadProgress.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        document.getElementById('uploadBtn').disabled = true;
        document.getElementById('uploadBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        
        console.log('Upload started - progress section now visible');
        
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
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            // Handle both JSON and text responses
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json().then(data => ({ data, response }));
            } else {
                return response.text().then(text => {
                    console.error('Non-JSON response:', text);
                    throw new Error('Server returned non-JSON response: ' + text.substring(0, 100));
                });
            }
        })
        .then(({ data, response }) => {
            console.log('Response data:', data);
            uploadProgress.classList.add('d-none');
            
            // Reset button state
            const uploadBtn = document.getElementById('uploadBtn');
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload & Parse CSV';
            
            if (data.success) {
                // Show success results
                document.getElementById('totalRecipients').textContent = data.total_recipients + ' recipients';
                document.getElementById('totalAmount').textContent = utils.formatCurrency(data.total_amount);
                
                // Show skipped rows if any
                if (data.skipped_rows && data.skipped_rows > 0) {
                    document.getElementById('skippedRowsCount').textContent = data.skipped_rows;
                    document.getElementById('skippedRowsInfo').style.display = 'block';
                } else {
                    document.getElementById('skippedRowsInfo').style.display = 'none';
                }
                
                uploadResults.classList.remove('d-none');
                currentBatchId = data.batch_id;
                
                // Scroll to results
                document.getElementById('uploadResults').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                
                // Show detailed success message
                let successMessage = `✅ CSV uploaded successfully!<br>`;
                successMessage += `📊 <strong>${data.valid_rows || data.total_recipients}</strong> valid recipients found<br>`;
                successMessage += `💰 Total amount: <strong>${utils.formatCurrency(data.total_amount)}</strong><br>`;
                if (data.skipped_rows && data.skipped_rows > 0) {
                    successMessage += `⚠️ <strong>${data.skipped_rows}</strong> rows skipped due to validation errors<br>`;
                }
                successMessage += `<br>Click "Start Token Distribution" to proceed.`;
                
                utils.showAlert('success', successMessage);
                
                // Update notification badge
                updateUploadNotificationBadge();
                
                // Reset form for next upload
                document.getElementById('uploadForm').reset();
                document.getElementById('fileInfo').classList.add('d-none');
                document.getElementById('uploadBtn').disabled = true;
            } else {
                let errorMessage = 'Upload failed: ';
                
                // Handle validation errors (from CSV parsing)
                if (data.validation_errors && Array.isArray(data.validation_errors)) {
                    errorMessage = 'CSV Validation Errors:<br>';
                    errorMessage += '<ul style="margin: 10px 0; padding-left: 20px;">';
                    data.validation_errors.forEach(err => {
                        errorMessage += `<li>${err}</li>`;
                    });
                    errorMessage += '</ul>';
                } else if (data.errors) {
                    if (typeof data.errors === 'object') {
                        errorMessage += Object.values(data.errors).flat().join(', ');
                    } else {
                        errorMessage += data.errors;
                    }
                } else {
                    errorMessage += (data.message || 'Unknown error');
                }
                
                // Show skipped rows info if available
                if (data.skipped_rows && data.skipped_rows > 0) {
                    errorMessage += `<br><small>⚠️ ${data.skipped_rows} rows were skipped due to validation errors.</small>`;
                }
                
                utils.showAlert('danger', errorMessage);
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            uploadProgress.classList.add('d-none');
            
            let errorMessage = 'Upload failed: ';
            
            // Handle different error types
            if (error.message) {
                if (error.message.includes('Failed to fetch')) {
                    errorMessage = 'Network error. Please check your internet connection and try again.';
                } else if (error.message.includes('CSRF')) {
                    errorMessage = 'Security token expired. Please refresh the page and try again.';
                } else {
                    errorMessage += error.message;
                }
            } else {
                errorMessage += 'Unknown error occurred. Please check your CSV file format and try again.';
            }
            
            errorMessage += '<br><small>💡 Make sure your CSV file has the required columns: name, address, phone_number, disco, meter_number, meter_type, amount</small>';
            
            utils.showAlert('danger', errorMessage);
            
            // Reset button state
            const uploadBtn = document.getElementById('uploadBtn');
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload & Parse CSV';
        });
    }

    function startProcessing(batchId = null) {
        const batch = batchId || currentBatchId;
        
        if (!batch) {
            utils.showAlert('danger', 'No batch selected for processing. Please upload a CSV file first.');
            return;
        }
        
        console.log('Starting processing for batch:', batch);
        
        const processingStatus = document.getElementById('processingStatus');
        const processBtn = document.getElementById('processBtn');
        const uploadResults = document.getElementById('uploadResults');
        
        if (processBtn) {
        processBtn.disabled = true;
        }
        
        if (processingStatus) {
            processingStatus.classList.remove('d-none');
        }
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;
        
        if (!csrfToken) {
            utils.showAlert('danger', 'CSRF token not found. Please refresh the page and try again.');
            if (processBtn) processBtn.disabled = false;
            if (processingStatus) processingStatus.classList.add('d-none');
            return;
        }
        
        console.log('CSRF Token found:', csrfToken ? 'Yes' : 'No');
        console.log('Process URL:', `/bulk-token/process/${batch}`);
        
        fetch(`/bulk-token/process/${batch}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Process response status:', response.status);
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || `HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Process response data:', data);
            if (data.success) {
                // Hide upload results
                if (uploadResults) {
                    uploadResults.classList.add('d-none');
                }
                
                // Show detailed success message
                let successMessage = `🚀 <strong>Token Distribution Started!</strong><br>`;
                successMessage += `📊 Processing <strong>${data.total_recipients || 'recipients'}</strong> recipients synchronously in batches<br>`;
                successMessage += `⏳ Each batch completes before the next starts (no queue)<br>`;
                successMessage += `⏳ This may take a few minutes to complete<br>`;
                successMessage += `<br>You can monitor progress below or in the History page.`;
                
                utils.showAlert('success', successMessage);
                
                // Scroll to processing status
                if (processingStatus) {
                    processingStatus.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                
                // Start polling for progress
                pollProcessingStatus();
            } else {
                utils.showAlert('danger', 'Failed to start processing: ' + (data.message || 'Unknown error'));
                if (processBtn) processBtn.disabled = false;
                if (processingStatus) processingStatus.classList.add('d-none');
            }
        })
        .catch(error => {
            console.error('Processing error:', error);
            
            // Enhanced error message handling
            let errorMessage = 'Failed to start processing: ';
            
            if (error.message) {
                errorMessage += error.message;
            } else if (error.response && error.response.data) {
                // Handle API error responses
                const errorData = error.response.data;
                if (errorData.message) {
                    errorMessage = errorData.message;
                } else if (errorData.error) {
                    errorMessage = errorData.error;
                } else if (errorData.errors) {
                    errorMessage += Object.values(errorData.errors).flat().join(', ');
                }
            } else {
                errorMessage += 'Unknown error occurred. Please try again.';
            }
            
            utils.showAlert('danger', errorMessage);
            if (processBtn) processBtn.disabled = false;
            if (processingStatus) processingStatus.classList.add('d-none');
        });
    }

    function pollProcessingStatus() {
        // Update batch progress info
        const batchProgressInfo = document.getElementById('batchProgressInfo');
        if (batchProgressInfo) {
            batchProgressInfo.innerHTML = '<i class="fas fa-info-circle"></i> Processing synchronously batch-by-batch (no async queue)';
        }
        const batchId = currentBatchId;
        if (!batchId) {
            console.error('No batch ID for polling');
            return;
        }
        
        const poll = () => {
            fetch(`/bulk-token/status/${batchId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        updateProgressUI(data.data);
                        
                        if (data.data.status === 'processing') {
                            setTimeout(poll, 3000); // Poll every 3 seconds
                        } else if (data.data.status === 'completed' || data.data.status === 'failed') {
                            showCompletionMessage(data.data);
                        }
                    } else {
                        console.error('Status polling failed:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error polling status:', error);
                    // Continue polling even on error
                    setTimeout(poll, 5000);
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
        
        const successRate = data.total_recipients > 0 
            ? Math.round((data.successful_transactions / data.total_recipients) * 100) 
            : 0;
        
        if (data.status === 'completed') {
            // Success notification with detailed info - flash for 10 seconds
            let message = `✅ <strong>Batch Processing Completed Successfully!</strong><br>`;
            message += `<strong>${data.successful_transactions}/${data.total_recipients}</strong> tokens sent successfully (${successRate}% success rate)<br>`;
            
            if (data.failed_transactions > 0) {
                message += `<strong>${data.failed_transactions}</strong> failed transactions<br>`;
                message += `<small>💡 Check the batch details page to see error messages for failed recipients.</small><br>`;
            }
            
            message += `An email notification has been sent to your registered email address.`;
            
            // Show notification for 10 seconds with flash effect
            utils.showAlert('success', message, 10000);
        } else if (data.status === 'failed') {
            // Failure notification with error details - flash for 10 seconds
            let message = `❌ <strong>Batch Processing Failed!</strong><br>`;
            message += `<strong>${data.processed_recipients || 0}/${data.total_recipients}</strong> recipients processed<br>`;
            message += `<strong>${data.successful_transactions || 0}</strong> successful, <strong>${data.failed_transactions || 0}</strong> failed<br>`;
            
            // Show error message if available
            if (data.error_message) {
                message += `<br><strong>Error:</strong> ${data.error_message}`;
            }
            
            message += `<br>💡 Check the batch details page to see detailed error messages for each recipient.`;
            message += `<br>An email notification has been sent to your registered email address with error details.`;
            
            // Show notification for 10 seconds with flash effect
            utils.showAlert('danger', message, 10000);
        } else {
            // Processing status update
            let message = `Processing status: <strong>${data.status}</strong><br>`;
            message += `${data.successful_transactions || 0}/${data.total_recipients} tokens processed`;
            
            if (data.failed_transactions > 0) {
                message += `, ${data.failed_transactions} failed`;
            }
            
            utils.showAlert('info', message, 10000);
        }
        
        // Refresh page after 10 seconds (after notification is shown)
        setTimeout(() => location.reload(), 10000);
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
                    
                    // Always show balance when API is connected
                    const balanceElement = document.getElementById('uploadStatusBalance');
                    const balanceAmount = document.getElementById('balanceAmount');
                    if (balanceElement && balanceAmount) {
                        if (data.balance !== null && data.balance !== undefined && data.balance !== '') {
                            const formattedBalance = new Intl.NumberFormat('en-NG', {
                                style: 'currency',
                                currency: 'NGN',
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }).format(data.balance);
                            balanceAmount.textContent = formattedBalance.replace('NGN', '₦');
                        } else {
                            balanceAmount.textContent = 'Not Available';
                        }
                        balanceElement.style.display = 'block';
                    }
                } else {
                    statusAlert.className = 'alert alert-warning d-flex align-items-center mb-4';
                    statusTitle.textContent = 'System Warning';
                    statusMessage.textContent = data.message || 'API connection issues detected';
                    statusSpinner.style.display = 'none';
                    
                    // Hide balance on error
                    const balanceElement = document.getElementById('uploadStatusBalance');
                    if (balanceElement) {
                        balanceElement.style.display = 'none';
                    }
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


    // Initialize notification polling
    setInterval(() => {
        updateUploadNotificationBadge();
        updateUploadStatus();
    }, 30000);

    // Initial load
    updateUploadNotificationBadge();
    updateUploadStatus();
</script>
@endpush
