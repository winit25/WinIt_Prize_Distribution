<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        // Regenerate session token on page load to prevent 419 errors
        if (!$request->session()->has('_token')) {
            $request->session()->regenerateToken();
        }
        
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'token' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            // Here we will attempt to reset the user's password. If it is successful we
            // will update the password on an actual user model and persist it to the
            // database. Otherwise we will parse the error and return the response.
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user) use ($request) {
                    // Update password and remember token
                    $user->password = Hash::make($request->password);
                    $user->remember_token = Str::random(60);
                    
                    // Clear the must_change_password flag if it exists
                    if (in_array('must_change_password', $user->getFillable())) {
                        $user->must_change_password = false;
                    }
                    
                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            // If the password was successfully reset, we will redirect the user back to
            // the application's home authenticated view. If there is an error we can
            // redirect them back to where they came from with their error message.
            if ($status == Password::PASSWORD_RESET) {
                \Log::info('Password reset successful', [
                    'email' => $request->email,
                    'ip' => $request->ip()
                ]);
                
                return redirect()->route('login')->with('status', __($status));
            } else {
                \Log::warning('Password reset failed', [
                    'email' => $request->email,
                    'status' => $status,
                    'ip' => $request->ip()
                ]);
                
                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => __($status)]);
            }
        } catch (\Exception $e) {
            \Log::error('Password reset error', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'An error occurred while resetting your password. Please try again.']);
        }
    }
}
