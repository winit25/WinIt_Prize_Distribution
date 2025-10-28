<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->load('roles');
        
        // Get user's recent activity
        $recentActivity = Cache::remember('user_activity_' . $user->id, 300, function () use ($user) {
            return \App\Models\ActivityLog::where('causer_id', $user->id)
                ->where('causer_type', \App\Models\User::class)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        });
        
        // Get user's batch uploads
        $recentBatches = Cache::remember('user_batches_' . $user->id, 300, function () use ($user) {
            return \App\Models\BatchUpload::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        });
        
        return view('profile.edit', [
            'user' => $user,
            'recentActivity' => $recentActivity,
            'recentBatches' => $recentBatches,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }
    
    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        
        // Log the password change
        Log::info('User password updated', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return Redirect::route('profile.edit')->with('status', 'password-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
