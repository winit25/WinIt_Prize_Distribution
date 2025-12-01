<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}" id="reset-password-form">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') ?? $request->input('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        // Ensure CSRF token is fresh when form is submitted
        document.addEventListener('DOMContentLoaded', function() {
            // Clear any stale session data on page load
            sessionStorage.clear();
            
            // Refresh CSRF token from meta tag
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) {
                const csrfInput = document.querySelector('input[name="_token"]');
                if (csrfInput) {
                    csrfInput.value = csrfMeta.getAttribute('content');
                }
            }
            
            // Handle 419 CSRF errors
            if (window.location.search.includes('419') || window.location.search.includes('csrf')) {
                sessionStorage.clear();
                setTimeout(function() {
                    window.location.href = window.location.pathname + window.location.search;
                }, 3000);
            }
            
            const form = document.getElementById('reset-password-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Get fresh CSRF token from meta tag
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (csrfToken) {
                        const tokenInput = this.querySelector('input[name="_token"]');
                        if (tokenInput) {
                            tokenInput.value = csrfToken.getAttribute('content');
                        }
                    }
                    // Allow form to submit normally
                });
            }
        });
    </script>
</x-guest-layout>
