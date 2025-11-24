@extends('layouts.sidebar')

@section('title', 'Dashboard - WinIt Prize Distribution')

@push('styles')
<style>
    .dashboard-card {
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(67, 19, 247, 0.1);
        border: 1px solid var(--winit-border);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    .dashboard-card:hover {
        box-shadow: 0 10px 25px rgba(67, 19, 247, 0.15);
        transform: translateY(-2px);
    }

    .stat-card {
        background: linear-gradient(135deg, var(--winit-primary) 0%, var(--winit-accent) 100%);
        border-radius: 1.5rem;
        padding: 2rem;
        color: white;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
        pointer-events: none;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 35px rgba(67, 19, 247, 0.3);
    }

    .stat-card.success {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    }

    .stat-card.warning {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
    }

    .stat-card.danger {
        background: linear-gradient(135deg, var(--winit-pink) 0%, #DC2626 100%);
    }

    .stat-card.info {
        background: linear-gradient(135deg, var(--winit-secondary) 0%, #0891B2 100%);
    }

    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.9;
    }

    .stat-content h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;
    }

    .stat-content p {
        font-size: 1rem;
        margin: 0.5rem 0 0 0;
        opacity: 0.9;
        font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;
    }

    .api-status-card {
        background: white;
        border-radius: 1.5rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(67, 19, 247, 0.1);
        border: 1px solid var(--winit-border);
        margin-bottom: 2rem;
    }

    .api-status-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
        font-weight: 600;
        color: var(--winit-dark);
        font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;
    }

    .api-status-header i {
        color: var(--winit-primary);
        font-size: 1.25rem;
    }

    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.875rem;
        font-weight: 500;
        font-family: 'Montserrat', sans-serif;
    }

    .status-indicator.success {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        color: white;
    }

    .status-indicator.error {
        background: linear-gradient(135deg, var(--winit-pink) 0%, #DC2626 100%);
        color: white;
    }

    .status-indicator.warning {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        color: white;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
    }

    .card-header {
        background: linear-gradient(135deg, var(--winit-primary) 0%, var(--winit-accent) 100%);
        color: white;
        border-radius: 1.5rem 1.5rem 0 0 !important;
        padding: 1.5rem 2rem;
        border: none;
        font-weight: 600;
        font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;
    }

    .card-body {
        padding: 2rem;
    }

    .list-group-item {
        border: none;
        border-bottom: 1px solid var(--winit-border);
        padding: 1rem 0;
    }

    .list-group-item:last-child {
        border-bottom: none;
    }

    .badge {
        padding: 0.5rem 1rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        font-family: 'Montserrat', sans-serif;
    }

    .badge-success {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        color: white;
    }

    .badge-warning {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        color: white;
    }

    .badge-info {
        background: linear-gradient(135deg, var(--winit-secondary) 0%, #0891B2 100%);
        color: white;
    }

    .btn-outline-primary.active {
        background: var(--winit-primary);
        color: white;
        border-color: var(--winit-primary);
    }

    .btn-group .btn {
        border-radius: 0.375rem !important;
        margin-right: 0.25rem;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    canvas {
        max-height: 300px;
    }
</style>
@endpush

@section('content')
<!-- API Integration Update Banner -->
<div class="alert alert-success mb-3" style="border-radius: 1.5rem; border: 2px solid #10b981; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);">
    <div class="d-flex align-items-center">
        <div class="flex-shrink-0 me-3">
            <i class="fas fa-rocket fa-2x" style="color: #10b981;"></i>
        </div>
        <div class="flex-grow-1">
            <h6 class="mb-1" style="color: #059669; font-weight: 700;">
                <i class="fas fa-bolt me-2"></i>System Upgraded: BuyPower API Integration Optimized
            </h6>
            <p class="mb-0" style="color: #065f46; font-size: 0.9rem;">
                Now using the <strong>/vend</strong> endpoint for faster token processing (~4-5 seconds per transaction).
                <a href="{{ route('bulk-token.index') }}" class="ms-2" style="color: #059669; text-decoration: underline;">
                    Learn more →
                </a>
            </p>
        </div>
        <div class="flex-shrink-0">
            <span class="badge" style="background: #10b981; color: white; font-size: 0.85rem; padding: 0.5rem 1rem; border-radius: 2rem;">
                <i class="fas fa-check-circle me-1"></i>Verified Working
            </span>
        </div>
    </div>
</div>

<!-- Dashboard Header Card -->
<div class="dashboard-card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2" style="color: var(--winit-dark); font-family: 'Montserrat Alternates', 'Montserrat', sans-serif; font-weight: 700;">
                    <i class="fas fa-tachometer-alt me-3" style="color: var(--winit-primary);"></i>Dashboard
                </h1>
                <p class="mb-0" style="color: var(--winit-gray); font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;">
                    Welcome to the WinIt Prize Distribution System
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="api-status-card">
                    <div class="api-status-header">
                        <i class="fas fa-plug"></i>
                        <span>API Status</span>
                        @if(config('buypower.use_mock', true))
                            <span class="badge badge-warning ms-auto">Sandbox Mode</span>
                        @else
                            <span class="badge badge-success ms-auto">Production Mode</span>
                        @endif
                    </div>
                    <div class="status-indicator" id="apiStatusIndicator">
                        <span class="status-dot"></span>
                        <span id="apiStatusText">Checking API Status...</span>
                    </div>
                    <small class="text-muted" id="lastUpdated">Last updated: {{ now()->format('H:i:s') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Primary Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $totalBatches }}</h3>
                <p>Total Batches</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>{{ number_format($totalRecipients) }}</h3>
                <p>Total Recipients</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="stat-content">
                <h3>{{ number_format($totalTransactions) }}</h3>
                <p>Total Transactions</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-naira-sign"></i>
            </div>
            <div class="stat-content">
                <h3>₦{{ number_format($totalAmount, 2) }}</h3>
                <p>Total Amount</p>
            </div>
        </div>
    </div>
</div>

<!-- Performance Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $successRate }}%</h3>
                <p>Success Rate</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $processingCount }}</h3>
                <p>Processing</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $failedCount }}</h3>
                <p>Failed</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="stat-content">
                <h3 id="accountBalance">Loading...</h3>
                <p>Account Balance</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="dashboard-card text-center">
            <div class="card-body">
                <div class="mb-3">
                    <i class="fas fa-upload fa-3x" style="color: var(--winit-primary);"></i>
                </div>
                <h5 style="font-family: 'Montserrat Alternates', 'Montserrat', sans-serif; font-weight: 600; color: var(--winit-dark);">
                    Upload CSV
                </h5>
                <p class="text-muted" style="font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;">
                    Upload recipient data for bulk processing
                </p>
                <a href="{{ route('bulk-token.index') }}" class="btn btn-primary">
                    <i class="fas fa-upload me-2"></i>Start Upload
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="dashboard-card text-center">
            <div class="card-body">
                <div class="mb-3">
                    <i class="fas fa-history fa-3x" style="color: var(--winit-accent);"></i>
                </div>
                <h5 style="font-family: 'Montserrat Alternates', 'Montserrat', sans-serif; font-weight: 600; color: var(--winit-dark);">
                    View History
                </h5>
                <p class="text-muted" style="font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;">
                    Check previous batch uploads and results
                </p>
                <a href="{{ route('bulk-token.history') }}" class="btn btn-primary">
                    <i class="fas fa-history me-2"></i>View History
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="dashboard-card text-center">
            <div class="card-body">
                <div class="mb-3">
                    <i class="fas fa-exchange-alt fa-3x" style="color: var(--winit-success);"></i>
                </div>
                <h5 style="font-family: 'Montserrat Alternates', 'Montserrat', sans-serif; font-weight: 600; color: var(--winit-dark);">
                    Transactions
                </h5>
                <p class="text-muted" style="font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;">
                    View all transaction details and status
                </p>
                <a href="{{ route('bulk-token.transactions') }}" class="btn btn-primary">
                    <i class="fas fa-exchange-alt me-2"></i>View Transactions
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="dashboard-card text-center">
            <div class="card-body">
                <div class="mb-3">
                    <i class="fas fa-user-cog fa-3x" style="color: var(--winit-warning);"></i>
                </div>
                <h5 style="font-family: 'Montserrat Alternates', 'Montserrat', sans-serif; font-weight: 600; color: var(--winit-dark);">
                    Profile
                </h5>
                <p class="text-muted" style="font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;">
                    Manage your account settings and preferences
                </p>
                <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                    <i class="fas fa-user-cog me-2"></i>Edit Profile
                </a>
            </div>
        </div>
    </div>
</div>

        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-lg-8 mb-4">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Transaction Analytics (Last 7 Days)</h5>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary active" id="transactionsChartBtn" onclick="switchChart('transactions')">
                                <i class="fas fa-list"></i> Transactions
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="amountsChartBtn" onclick="switchChart('amounts')">
                                <i class="fas fa-money-bill"></i> Amounts
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="transactionsChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-pie-chart me-2"></i>Status Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" width="300" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Cards -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Batches</h5>
                    </div>
                    <div class="card-body">
                        @if($recentBatches->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($recentBatches as $batch)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                <h6 class="mb-1" style="font-family: 'Montserrat Alternates', 'Montserrat', sans-serif; font-weight: 600; color: var(--winit-dark);">
                                    {{ $batch->batch_name }}
                                </h6>
                                <small class="text-muted" style="font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;">
                                                    {{ $batch->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                            <span class="badge badge-{{ $batch->status === 'completed' ? 'success' : ($batch->status === 'processing' ? 'warning' : 'info') }}">
                                                {{ ucfirst($batch->status) }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted" style="font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;">No batches found.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
    <div class="col-lg-6 mb-4">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-activity me-2"></i>Recent Activity</h5>
            </div>
            <div class="card-body">
                @if($recentActivity->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentActivity as $activity)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                <h6 class="mb-1" style="font-family: 'Montserrat Alternates', 'Montserrat', sans-serif; font-weight: 600; color: var(--winit-dark);">
                                    {{ $activity->event ?? 'System Activity' }}
                                </h6>
                                <small class="text-muted" style="font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;">
                                            {{ $activity->description }}
                                        </small>
                                    </div>
                                <small class="text-muted" style="font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;">
                                    {{ $activity->created_at->diffForHumans() }}
                                </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                <p class="text-muted" style="font-family: 'Montserrat Alternates', 'Montserrat', sans-serif;">No recent activity.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // API Status Check
    async function updateDashboardData() {
        try {
            // Fetch API status with balance
            const apiResponse = await fetch('/api-status-public', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (apiResponse.ok) {
                const apiData = await apiResponse.json();
                
                // Update API status
                updateApiStatus({
                    status: apiData.status || (apiData.success ? 'success' : 'error'),
                    message: apiData.message || 'API Status Unknown'
                });
                
                // Update balance
                updateBalance(apiData.balance);
            }

            // Fetch dashboard statistics
            const statsResponse = await fetch('/dashboard-live-data-public', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (statsResponse.ok) {
                const statsData = await statsResponse.json();
                
                // Update statistics if available
                if (statsData.statistics) {
                    updateStatistics(statsData.statistics);
                }
            }
        } catch (error) {
            console.error('Error updating dashboard:', error);
            updateApiStatus({
                status: 'error',
                message: 'Connection failed: ' + (error.message || 'Unknown error')
            });
            updateBalance(null);
        }
    }

    function updateBalance(balance) {
        const balanceElement = document.getElementById('accountBalance');
        if (balanceElement) {
            if (balance !== null && balance !== undefined) {
                // Format balance as currency
                const formattedBalance = new Intl.NumberFormat('en-NG', {
                    style: 'currency',
                    currency: 'NGN',
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(balance);
                balanceElement.textContent = formattedBalance.replace('NGN', '₦');
            } else {
                balanceElement.textContent = 'Not Available';
                balanceElement.style.fontSize = '1.5rem';
            }
        }
    }

    function updateApiStatus(apiStatus) {
        const indicator = document.getElementById('apiStatusIndicator');
        const text = document.getElementById('apiStatusText');
        const lastUpdated = document.getElementById('lastUpdated');

        if (apiStatus.status === 'success') {
            if (indicator) indicator.className = 'status-indicator success';
            if (text) text.textContent = 'API Connected';
        } else if (apiStatus.status === 'error') {
            if (indicator) indicator.className = 'status-indicator error';
            if (text) text.textContent = 'API Error: ' + apiStatus.message;
        } else {
            if (indicator) indicator.className = 'status-indicator warning';
            if (text) text.textContent = 'API Status Unknown';
        }

        if (lastUpdated) {
            lastUpdated.textContent = 'Last updated: ' + new Date().toLocaleTimeString();
        }
    }

    function updateStatistics(stats) {
        // Update statistics if needed
        console.log('Statistics updated:', stats);
    }

    // Update dashboard data every 30 seconds
    setInterval(updateDashboardData, 30000);

    // Initial load
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial balance to "Not Available"
        updateBalance(null);
        
        // Load dashboard data
        updateDashboardData();
        
        // Fallback to show offline mode after 5 seconds if no response
        setTimeout(() => {
            const text = document.getElementById('apiStatusText');
            if (text && text.textContent === 'Checking API Status...') {
                updateApiStatus({
                    status: 'warning',
                    message: 'Using offline mode'
                });
            }
        }, 5000);
    });

    // Chart.js Implementation
    let transactionsChart, statusChart;
    let currentChartType = 'transactions';

    // Chart data from Laravel
    const chartData = @json($chartData);

    // Initialize charts when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
    });

    function initializeCharts() {
        // Transactions/Amounts Bar Chart
        const ctx = document.getElementById('transactionsChart').getContext('2d');
        transactionsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Transactions',
                    data: chartData.dailyTransactions,
                    backgroundColor: 'rgba(67, 19, 247, 0.8)',
                    borderColor: 'rgba(67, 19, 247, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(67, 19, 247, 0.9)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: 'rgba(67, 19, 247, 1)',
                        borderWidth: 1,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(67, 19, 247, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(67, 19, 247, 0.7)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: 'rgba(67, 19, 247, 0.7)'
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Status Distribution Pie Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusLabels = Object.keys(chartData.statusDistribution);
        const statusData = Object.values(chartData.statusDistribution);
        const statusColors = {
            'success': 'rgba(5, 150, 105, 0.8)',
            'failed': 'rgba(220, 38, 38, 0.8)',
            'pending': 'rgba(217, 119, 6, 0.8)',
            'processing': 'rgba(37, 99, 235, 0.8)'
        };

        statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),
                datasets: [{
                    data: statusData,
                    backgroundColor: statusLabels.map(label => statusColors[label] || 'rgba(107, 114, 128, 0.8)'),
                    borderColor: statusLabels.map(label => statusColors[label]?.replace('0.8', '1') || 'rgba(107, 114, 128, 1)'),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: 'rgba(67, 19, 247, 0.7)',
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(67, 19, 247, 0.9)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: 'rgba(67, 19, 247, 1)',
                        borderWidth: 1,
                        cornerRadius: 8
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    function switchChart(type) {
        currentChartType = type;
        
        // Update button states
        document.getElementById('transactionsChartBtn').classList.toggle('active', type === 'transactions');
        document.getElementById('amountsChartBtn').classList.toggle('active', type === 'amounts');
        
        // Update chart data
        if (transactionsChart) {
            if (type === 'transactions') {
                transactionsChart.data.datasets[0].data = chartData.dailyTransactions;
                transactionsChart.data.datasets[0].label = 'Transactions';
                transactionsChart.data.datasets[0].backgroundColor = 'rgba(67, 19, 247, 0.8)';
                transactionsChart.data.datasets[0].borderColor = 'rgba(67, 19, 247, 1)';
            } else {
                transactionsChart.data.datasets[0].data = chartData.dailyAmounts;
                transactionsChart.data.datasets[0].label = 'Amount (₦)';
                transactionsChart.data.datasets[0].backgroundColor = 'rgba(23, 247, 182, 0.8)';
                transactionsChart.data.datasets[0].borderColor = 'rgba(23, 247, 182, 1)';
            }
            transactionsChart.update('active');
        }
    }

    // Refresh charts every 5 minutes
    setInterval(() => {
        if (transactionsChart && statusChart) {
            // You could fetch new data here and update charts
            console.log('Charts refreshed');
        }
    }, 300000);
</script>
@endpush
