<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    /**
     * Get the roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        
        return $this->roles->contains($role);
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole($roles)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }
        
        return $this->roles->whereIn('name', $roles)->isNotEmpty();
    }

    /**
     * Get the batch uploads for the user.
     */
    public function batchUploads()
    {
        return $this->hasMany(BatchUpload::class);
    }

    /**
     * Get the activity logs for the user.
     */
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'causer');
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission($permission)
    {
        // Super admin has all permissions
        if ($this->hasRole('super-admin') || $this->hasRole('Super Admin')) {
            return true;
        }
        
        // Check if any of the user's roles have this permission
        return $this->roles->flatMap->permissions->contains('name', $permission);
    }

    /**
     * Check if user can perform bulk token operations.
     */
    public function canUploadCsv()
    {
        return $this->hasPermission('upload-csv') || $this->hasRole('super-admin') || $this->hasRole('Super Admin');
    }

    /**
     * Check if user can manage users.
     */
    public function canManageUsers()
    {
        return $this->hasPermission('manage-users') || $this->hasRole('super-admin') || $this->hasRole('Super Admin');
    }

    /**
     * Check if user can view transactions.
     */
    public function canViewTransactions()
    {
        return $this->hasPermission('view-transactions') || $this->hasRole('super-admin') || $this->hasRole('Super Admin');
    }

    /**
     * Check if user can only audit (view-only access).
     */
    public function isAuditOnly()
    {
        return $this->hasRole('audit') && !$this->hasRole('super-admin');
    }
}
