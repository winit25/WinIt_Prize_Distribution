<?php

namespace App\Http\Controllers;

use App\Models\DeviceFingerprint;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DeviceFingerprintController extends Controller
{
    /**
     * Display a listing of device fingerprints.
     * Only accessible by admins.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user || (!$user->canManageUsers() && !$user->hasRole('super-admin'))) {
            abort(403, 'You do not have permission to view device fingerprints.');
        }

        $query = DeviceFingerprint::with('user')->orderBy('created_at', 'desc');

        // Filter by user if specified - validate input to prevent SQL injection
        if ($request->filled('user_id')) {
            $userId = filter_var($request->user_id, FILTER_VALIDATE_INT);
            if ($userId !== false && $userId > 0) {
                $query->where('user_id', $userId);
            }
        }

        // Filter by active status - validate boolean
        if ($request->filled('is_active')) {
            $isActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }
        }

        $devices = $query->paginate(20);
        $users = User::orderBy('name')->get();

        return view('admin.device-fingerprints.index', compact('devices', 'users'));
    }

    /**
     * Show details of a specific device fingerprint.
     */
    public function show(DeviceFingerprint $deviceFingerprint)
    {
        $user = request()->user();
        
        if (!$user || (!$user->canManageUsers() && !$user->hasRole('super-admin'))) {
            abort(403, 'You do not have permission to view device fingerprints.');
        }

        $deviceFingerprint->load('user');

        return view('admin.device-fingerprints.show', compact('deviceFingerprint'));
    }

    /**
     * Deactivate a device fingerprint (admin override).
     */
    public function deactivate(DeviceFingerprint $deviceFingerprint)
    {
        $user = request()->user();
        
        if (!$user || (!$user->canManageUsers() && !$user->hasRole('super-admin'))) {
            abort(403, 'You do not have permission to manage device fingerprints.');
        }

        $deviceFingerprint->update(['is_active' => false]);

        // Log the action
        try {
            app(\App\Services\ActivityLoggingService::class)->log(
                'device_deactivated',
                "Device fingerprint deactivated for user: {$deviceFingerprint->user->name}",
                $deviceFingerprint->user,
                [
                    'device_id' => $deviceFingerprint->id,
                    'device_name' => $deviceFingerprint->device_name,
                ]
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to log device deactivation', ['error' => $e->getMessage()]);
        }

        return redirect()->route('device-fingerprints.index')
            ->with('success', 'Device fingerprint deactivated successfully.');
    }

    /**
     * Reset device fingerprint for a user (allows new device registration).
     */
    public function resetUserDevice(User $user)
    {
        $admin = request()->user();
        
        if (!$admin || (!$admin->canManageUsers() && !$admin->hasRole('super-admin'))) {
            abort(403, 'You do not have permission to reset device fingerprints.');
        }

        // Deactivate all devices for this user
        DeviceFingerprint::where('user_id', $user->id)
            ->update(['is_active' => false]);

        // Log the action
        try {
            app(\App\Services\ActivityLoggingService::class)->log(
                'device_reset',
                "Device fingerprint reset for user: {$user->name}",
                $user,
                [
                    'reset_by' => $admin->id,
                ]
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to log device reset', ['error' => $e->getMessage()]);
        }

        return redirect()->route('device-fingerprints.index')
            ->with('success', "Device fingerprint reset for {$user->name}. They can now register a new device on next login.");
    }

    /**
     * Delete a device fingerprint.
     */
    public function destroy(DeviceFingerprint $deviceFingerprint)
    {
        $user = request()->user();
        
        if (!$user || (!$user->canManageUsers() && !$user->hasRole('super-admin'))) {
            abort(403, 'You do not have permission to delete device fingerprints.');
        }

        $deviceName = $deviceFingerprint->device_name;
        $userId = $deviceFingerprint->user_id;
        
        $deviceFingerprint->delete();

        // Log the action
        try {
            $targetUser = User::find($userId);
            if ($targetUser) {
                app(\App\Services\ActivityLoggingService::class)->log(
                    'device_deleted',
                    "Device fingerprint deleted: {$deviceName}",
                    $targetUser,
                    [
                        'device_name' => $deviceName,
                    ]
                );
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to log device deletion', ['error' => $e->getMessage()]);
        }

        return redirect()->route('device-fingerprints.index')
            ->with('success', 'Device fingerprint deleted successfully.');
    }
}
