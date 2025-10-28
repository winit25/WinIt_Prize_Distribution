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
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = Auth::user();
        
        // Update password and remove the must_change_password flag
        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        // Log the password change
        Log::info('User password changed', [
            'user_id' => $user->id,
            'email' => $user->email,
            'changed_at' => now()
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Password updated successfully! You can now access all features.');
    }
}
