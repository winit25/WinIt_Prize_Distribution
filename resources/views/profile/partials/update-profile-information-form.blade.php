<form method="post" action="{{ route('profile.update') }}" class="needs-validation" novalidate>
    @csrf
    @method('patch')

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" 
                   class="form-control @error('name') is-invalid @enderror" 
                   id="name" 
                   name="name" 
                   value="{{ old('name', $user->name) }}" 
                   required 
                   autofocus 
                   autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   id="email" 
                   name="email" 
                   value="{{ old('email', $user->email) }}" 
                   required 
                   autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-muted">
                        Your email address is unverified.
                        <button form="send-verification" class="btn btn-link p-0 text-decoration-underline">
                            Click here to re-send the verification email.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success mt-2">
                            A new verification link has been sent to your email address.
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Save Changes
        </button>
    </div>
</form>

<form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-none">
    @csrf
</form>
