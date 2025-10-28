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

            // Send password notification email
            try {
                Mail::to($user->email)->send(new UserPasswordNotification($user, $temporaryPassword));
                
                Log::info('Password email sent successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'created_by' => auth()->id()
                ]);
            } catch (\Exception $emailException) {
                Log::error('Failed to send password email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $emailException->getMessage()
                ]);
                
                // Continue with user creation even if email fails
                return redirect()->route('users.index')
                    ->with('success', 'User created successfully! However, the password email failed to send. Please contact the user directly.')
                    ->with('warning', 'Password: ' . $temporaryPassword);
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
    public function edit(User $user)
    {
        $roles = Role::all();
        $user->load('roles');
        
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
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
     * Remove the specified user
     */
    public function destroy(User $user)
    {
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

