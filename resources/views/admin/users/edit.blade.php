@extends('layouts.sidebar')

@section('title', 'Edit User - WinIt Prize Distribution')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(18, 18, 104, 0.1);
        border: 1px solid rgba(18, 18, 104, 0.1);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    .form-card:hover {
        box-shadow: 0 10px 25px rgba(18, 18, 104, 0.15);
        transform: translateY(-2px);
    }

    .form-header {
        background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
        color: white;
        padding: 2rem;
        text-align: center;
    }

    .form-header h1 {
        margin: 0;
        font-weight: 700;
        font-size: 1.75rem;
    }

    .form-header p {
        margin: 0.5rem 0 0 0;
        opacity: 0.9;
    }

    .form-body {
        padding: 2rem;
    }

    .form-label {
        font-weight: 600;
        color: rgb(18, 18, 104);
        margin-bottom: 0.5rem;
    }

    .form-control {
        border: 2px solid rgba(18, 18, 104, 0.1);
        border-radius: 0.75rem;
        padding: 0.875rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8fafc;
    }

    .form-control:focus {
        border-color: rgb(18, 18, 104);
        box-shadow: 0 0 0 4px rgba(18, 18, 104, 0.1);
        background: white;
        outline: none;
    }

    .form-check-input:checked {
        background-color: rgb(18, 18, 104);
        border-color: rgb(18, 18, 104);
    }

    .form-check-input:focus {
        box-shadow: 0 0 0 0.25rem rgba(18, 18, 104, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, rgb(18, 18, 104) 0%, rgb(30, 30, 120) 100%);
        border: none;
        border-radius: 0.75rem;
        padding: 0.875rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(18, 18, 104, 0.3);
        background: linear-gradient(135deg, rgb(12, 12, 80) 0%, rgb(18, 18, 104) 100%);
    }

    .btn-secondary {
        background: #6b7280;
        border: none;
        border-radius: 0.75rem;
        padding: 0.875rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: #4b5563;
        transform: translateY(-1px);
    }

    .role-card {
        background: #f8fafc;
        border: 2px solid rgba(18, 18, 104, 0.1);
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .role-card:hover {
        border-color: rgb(18, 18, 104);
        background: rgba(18, 18, 104, 0.05);
    }

    .role-card.selected {
        border-color: rgb(18, 18, 104);
        background: rgba(18, 18, 104, 0.1);
    }

    .role-checkbox {
        display: none;
    }

    .alert {
        border-radius: 0.75rem;
        border: none;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }

    .alert-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border-left: 4px solid #ef4444;
    }

    .password-toggle {
        position: relative;
    }

    .password-toggle .btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6b7280;
        padding: 0;
        font-size: 0.875rem;
    }

    .password-toggle .btn:hover {
        color: rgb(18, 18, 104);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-card">
                <div class="form-header">
                    <h1><i class="fas fa-user-edit me-3"></i>Edit User</h1>
                    <p>Update user information and roles</p>
                </div>

                <div class="form-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="form-label">Full Name</label>
                            <input id="name" 
                                   type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   required 
                                   autofocus
                                   placeholder="Enter full name">
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" 
                                   type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   required
                                   placeholder="Enter email address">
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label">New Password</label>
                            <div class="password-toggle">
                                <input id="password" 
                                       type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       name="password"
                                       placeholder="Leave blank to keep current password">
                                <button type="button" class="btn" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Leave blank to keep the current password</small>
                        </div>

                        <!-- Password Confirmation -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <div class="password-toggle">
                                <input id="password_confirmation" 
                                       type="password" 
                                       class="form-control" 
                                       name="password_confirmation"
                                       placeholder="Confirm new password">
                                <button type="button" class="btn" onclick="togglePassword('password_confirmation')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Roles -->
                        <div class="mb-4">
                            <label class="form-label">Assign Roles</label>
                            <div class="row">
                                @foreach($roles as $role)
                                    <div class="col-md-6 mb-2">
                                        <div class="role-card" onclick="toggleRole({{ $role->id }})">
                                            <div class="form-check">
                                                <input class="form-check-input role-checkbox" 
                                                       type="checkbox" 
                                                       name="roles[]" 
                                                       value="{{ $role->id }}" 
                                                       id="role_{{ $role->id }}"
                                                       {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="role_{{ $role->id }}">
                                                    {{ $role->name }}
                                                </label>
                                                @if($role->description)
                                                    <p class="text-muted small mb-0 mt-1">{{ $role->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>Update User
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary flex-fill">
                                <i class="fas fa-arrow-left me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleRole(roleId) {
    const checkbox = document.getElementById('role_' + roleId);
    const card = checkbox.closest('.role-card');
    
    checkbox.checked = !checkbox.checked;
    
    if (checkbox.checked) {
        card.classList.add('selected');
    } else {
        card.classList.remove('selected');
    }
}

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Initialize selected roles on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.role-checkbox:checked').forEach(function(checkbox) {
        checkbox.closest('.role-card').classList.add('selected');
    });
});
</script>
@endsection
