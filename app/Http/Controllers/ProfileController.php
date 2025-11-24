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
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }
        
        try {
            $user->load(['roles.permissions']);
        } catch (\Exception $e) {
            Log::error('Failed to load user roles', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            // Continue with empty roles if loading fails
        }
        
        // Get all permissions through roles
        $userPermissions = collect();
        if ($user->roles && $user->roles->isNotEmpty()) {
            foreach ($user->roles as $role) {
                if ($role->permissions) {
                    $userPermissions = $userPermissions->merge($role->permissions);
                }
            }
        }
        $userPermissions = $userPermissions->unique('id')->sortBy('category');
        
        // Group permissions by category
        $permissionsByCategory = $userPermissions->groupBy('category');
        
        // Get user's recent activity
        try {
            $recentActivity = Cache::remember('user_activity_' . $user->id, 300, function () use ($user) {
                return \App\Models\ActivityLog::where('causer_id', $user->id)
                    ->where('causer_type', \App\Models\User::class)
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            });
        } catch (\Exception $e) {
            Log::error('Failed to load user activity', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            $recentActivity = collect();
        }
        
        // Get user's batch uploads
        try {
            $recentBatches = Cache::remember('user_batches_' . $user->id, 300, function () use ($user) {
                return \App\Models\BatchUpload::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            });
        } catch (\Exception $e) {
            Log::error('Failed to load user batches', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            $recentBatches = collect();
        }
        
        return view('profile.edit', [
            'user' => $user,
            'userPermissions' => $userPermissions,
            'permissionsByCategory' => $permissionsByCategory,
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
        
        // Log the password change activity
        try {
            app(\App\Services\ActivityLoggingService::class)->logPasswordChanged($user);
        } catch (\Exception $e) {
            Log::warning('Failed to log password change activity', ['error' => $e->getMessage()]);
        }
        
        // Log the password change
        Log::info('User password updated', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return Redirect::route('profile.edit')->with('status', 'password-updated');
    }

    /**
     * Delete the user's account.
     * Only superadmins can delete accounts (including their own).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        // Only superadmins can delete accounts
        if (!$user->hasRole('super-admin') && !$user->hasRole('Super Admin')) {
            abort(403, 'Only super administrators can delete user accounts.');
        }
        
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
