<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PasswordChangeController extends Controller
{
    /**
     * Show the password change form
     */
    public function show()
    {
        return view('auth.password-change');
    }

    /**
     * Update the user's password
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        // For users who must change password (first-time login), don't require current password
        if ($user->must_change_password) {
            $request->validate([
                'password' => ['required', 'confirmed', 'min:8'],
            ]);
        } else {
            // For regular password changes, require current password
            $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', 'min:8'],
            ]);
        }
        
        $wasFirstTime = $user->must_change_password;
        
        // Update password and remove the must_change_password flag
        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        
        // Mark user as active (verified) if this is their first password change
        if ($wasFirstTime && !$user->email_verified_at) {
            $user->email_verified_at = now();
        }
        
        $user->save();
        
        // Refresh user session to reflect changes immediately
        Auth::login($user);

        // Log the password change activity
        try {
            app(\App\Services\ActivityLoggingService::class)->logPasswordChanged($user);
        } catch (\Exception $e) {
            Log::warning('Failed to log password change activity', ['error' => $e->getMessage()]);
        }

        // Log the password change
        Log::info('User password changed', [
            'user_id' => $user->id,
            'email' => $user->email,
            'first_time' => $wasFirstTime,
            'email_verified' => $user->email_verified_at ? 'yes' : 'no',
            'changed_at' => now()
        ]);

        $successMessage = 'Password updated successfully!';
        if ($wasFirstTime && $user->email_verified_at) {
            $successMessage .= ' Your account has been activated.';
        }

        return redirect()->route('dashboard')
            ->with('success', $successMessage);
    }
}
