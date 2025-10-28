@extends('layouts.sidebar')

@section('title', 'Profile - BuyPower')

@section('content')
<div class="content-header">
    <h1><i class="fas fa-user me-3"></i>My Profile</h1>
    <p>Manage your account settings and preferences</p>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Profile Information</h5>
            </div>
            <div class="card-body text-center">
                <div class="profile-avatar mb-3">
                    <div class="avatar-circle">
                        <i class="fas fa-user fa-2x"></i>
                    </div>
                </div>
                <h4>{{ auth()->user()->name }}</h4>
                <p class="text-muted">{{ auth()->user()->email }}</p>
                <p class="text-muted">
                    <small>Member since {{ auth()->user()->created_at->format('M Y') }}</small>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Profile</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="department" class="form-label">Department</label>
                        <input type="text" class="form-control @error('department') is-invalid @enderror" 
                               id="department" name="department" value="{{ old('department', auth()->user()->department) }}">
                        @error('department')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-key me-2"></i>Change Password</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                               id="current_password" name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" 
                               id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key me-2"></i>Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Account Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $userBatches ?? 0 }}</h4>
                        <p class="text-muted mb-0">Batches Created</p>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $userTransactions ?? 0 }}</h4>
                        <p class="text-muted mb-0">Transactions</p>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-info">₦{{ number_format($userAmount ?? 0, 2) }}</h4>
                        <p class="text-muted mb-0">Total Amount</p>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning">{{ $userSuccessRate ?? 0 }}%</h4>
                        <p class="text-muted mb-0">Success Rate</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-avatar {
    display: flex;
    justify-content: center;
}

.avatar-circle {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
}

.form-control {
    border: 2px solid var(--border-light);
    border-radius: 0.75rem;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: var(--bg-light);
}

.form-control:focus {
    border-color: var(--secondary-blue);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    background: white;
}

.form-control.is-invalid {
    border-color: #dc2626;
}

.invalid-feedback {
    display: block;
    color: #dc2626;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
</style>
@endsection
