<form method="post" action="{{ route('profile.password.update') }}" class="needs-validation" novalidate>
    @csrf
    @method('put')

    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="current_password" class="form-label">Current Password</label>
            <input type="password" 
                   class="form-control @error('current_password') is-invalid @enderror" 
                   id="current_password" 
                   name="current_password" 
                   required 
                   autocomplete="current-password">
            @error('current_password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="password" class="form-label">New Password</label>
            <input type="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   id="password" 
                   name="password" 
                   required 
                   autocomplete="new-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="password_confirmation" class="form-label">Confirm New Password</label>
            <input type="password" 
                   class="form-control" 
                   id="password_confirmation" 
                   name="password_confirmation" 
                   required 
                   autocomplete="new-password">
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-key me-2"></i>Update Password
        </button>
    </div>
</form>
