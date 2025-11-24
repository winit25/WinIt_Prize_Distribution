<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Services\PasswordGeneratorService;
use App\Mail\UserPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = User::with('roles')->orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Generate a temporary password
            $passwordGenerator = new PasswordGeneratorService();
            $temporaryPassword = $passwordGenerator->generateTemporaryPassword();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($temporaryPassword),
                'must_change_password' => true, // Force password change on first login
            ]);

            // Assign roles
            $user->roles()->sync($request->roles);

            // Send password notification email synchronously
            try {
                // Use Mail::send() directly to ensure synchronous delivery
                $fromAddress = config('mail.mailers.smtp.username') ?: config('mail.from.address');
                $fromName = config('mail.from.name');
                
                Mail::send('emails.user-password-notification', [
                    'user' => $user,
                    'password' => $temporaryPassword,
                    'loginUrl' => url('/login'),
                ], function ($message) use ($user, $fromAddress, $fromName) {
                    $message->to($user->email)
                            ->from($fromAddress, $fromName)
                            ->replyTo($fromAddress, $fromName)
                            ->subject('Your WinIt Prize Distribution Account Credentials - Action Required');
                });
                
                Log::info('Password email sent successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'created_by' => auth()->id()
                ]);
            } catch (\Exception $emailException) {
                Log::error('Failed to send password email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $emailException->getMessage(),
                    'trace' => $emailException->getTraceAsString()
                ]);
                
                // Continue with user creation even if email fails
                return redirect()->route('users.index')
                    ->with('success', 'User created successfully! However, the password email failed to send. Please contact the user directly.')
                    ->with('warning', 'Password: ' . $temporaryPassword);
            }

            // Log user creation activity
            try {
                app(\App\Services\ActivityLoggingService::class)->logUserCreated($user);
            } catch (\Exception $e) {
                Log::warning('Failed to log user creation activity', ['error' => $e->getMessage()]);
            }

            Log::info('User created', [
                'user_id' => $user->id,
                'email' => $user->email,
                'created_by' => auth()->id()
            ]);

            return redirect()->route('users.index')
                ->with('success', 'User created successfully! Password has been sent to their email address.');
        } catch (\Exception $e) {
            Log::error('User creation failed', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return redirect()->route('users.index')
                ->with('error', 'User not found.');
        }
        
        $roles = Role::all();
        $user->load('roles');
        
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return redirect()->route('users.index')
                ->with('error', 'User not found.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
            ]);

            // Update roles
            $user->roles()->sync($request->roles);

            Log::info('User updated', [
                'user_id' => $user->id,
                'email' => $user->email,
                'updated_by' => auth()->id()
            ]);

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully!');
        } catch (\Exception $e) {
            Log::error('User update failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::find($id);
        
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.'
                ], 404);
            }
            return redirect()->route('users.index')
                ->with('error', 'User not found.');
        }
        
        try {
            // Idempotency window: prevent multiple resets within 60s
            $cacheKey = 'password_reset_lock_user_' . $user->id;
            if (cache()->has($cacheKey)) {
                $message = 'A password reset was just sent. Please wait a minute before trying again.';
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 429);
                }
                return redirect()->route('users.index')->with('warning', $message);
            }

            // Place a short-lived lock
            cache()->put($cacheKey, true, now()->addSeconds(60));

            // Generate a new temporary password
            $passwordGenerator = new PasswordGeneratorService();
            $temporaryPassword = $passwordGenerator->generateTemporaryPassword();

            // Update user password
            $user->update([
                'password' => Hash::make($temporaryPassword),
                'must_change_password' => true,
            ]);

            // Send password notification email synchronously
            try {
                // Use Mail::send() directly to ensure synchronous delivery
                $fromAddress = config('mail.mailers.smtp.username') ?: config('mail.from.address');
                $fromName = config('mail.from.name');
                
                Mail::send('emails.user-password-notification', [
                    'user' => $user,
                    'password' => $temporaryPassword,
                    'loginUrl' => url('/login'),
                ], function ($message) use ($user, $fromAddress, $fromName) {
                    $message->to($user->email)
                            ->from($fromAddress, $fromName)
                            ->replyTo($fromAddress, $fromName)
                            ->subject('Your WinIt Prize Distribution Account Credentials - Action Required');
                });
                
                Log::info('Password reset email sent successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'reset_by' => auth()->id()
                ]);

                // Activity log
                try {
                    app(\App\Services\ActivityLoggingService::class)->log(
                        'password_reset_sent',
                        "Password reset email sent to {$user->email}",
                        $user,
                        [ 'reset_by' => auth()->id() ]
                    );
                } catch (\Throwable $e) {}

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Password reset successfully! New password has been sent to the user\'s email.'
                    ]);
                }

                return redirect()->route('users.index')
                    ->with('success', 'Password reset sent once. The user will receive an email shortly.');
            } catch (\Exception $emailException) {
                Log::error('Failed to send password reset email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $emailException->getMessage()
                ]);
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Password reset updated. However, the email failed to send. Please contact the user directly.',
                        'warning' => 'New Password: ' . $temporaryPassword
                    ]);
                }

                return redirect()->route('users.index')
                    ->with('success', 'Password reset updated. However, the email failed to send. Please contact the user directly.')
                    ->with('warning', 'New Password: ' . $temporaryPassword);
            }
        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to reset password: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to reset password: ' . $e->getMessage());
        }
    }

    /**
     * Get users data for API
     */
    public function apiIndex(Request $request)
    {
        try {
            $users = User::with('roles')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
                'stats' => [
                    'total' => $users->total(),
                    'active' => $users->where('email_verified_at', '!=', null)->count(),
                    'inactive' => $users->where('email_verified_at', null)->count(),
                    'admins' => $users->filter(function($user) { 
                        return $user->hasRole('super-admin') || $user->hasRole('Super Admin'); 
                    })->count(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch users data', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch users data'
            ], 500);
        }
    }

    /**
     * Remove the specified user from storage.
     * Only superadmins can delete users.
     */
    public function destroy($id)
    {
        $currentUser = auth()->user();
        
        // Only superadmins can delete users
        if (!$currentUser || (!$currentUser->hasRole('super-admin') && !$currentUser->hasRole('Super Admin'))) {
            abort(403, 'Only super administrators can delete user accounts.');
        }
        
        $user = User::find($id);
        
        if (!$user) {
            return redirect()->route('users.index')
                ->with('error', 'User not found.');
        }
        
        try {
            // Prevent self-deletion
            if ($user->id === auth()->id()) {
                return redirect()->back()
                    ->with('error', 'You cannot delete your own account!');
            }

            Log::info('User deleted', [
                'user_id' => $user->id,
                'email' => $user->email,
                'deleted_by' => auth()->id()
            ]);

            $user->delete();

            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            Log::error('User deletion failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}

