<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status == Password::RESET_LINK_SENT) {
                Log::info('Password reset link sent', [
                    'email' => $request->email,
                    'ip' => $request->ip()
                ]);
                
                return back()->with('status', __($status));
            } else {
                Log::warning('Password reset link failed', [
                    'email' => $request->email,
                    'status' => $status,
                    'ip' => $request->ip()
                ]);
                
                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => __($status)]);
            }
        } catch (\Exception $e) {
            Log::error('Password reset link error', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Unable to send password reset link. Please try again later.']);
        }
    }
}
