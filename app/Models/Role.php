<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'display_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    // Accessors
    public function getStatusBadgeAttribute(): string
    {
        $badgeClass = $this->is_active ? 'bg-success' : 'bg-danger';
        $status = $this->is_active ? 'Active' : 'Inactive';
        return "<span class='badge {$badgeClass}'>" . $status . "</span>";
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    // Helper methods
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }

    public function assignPermission(Permission $permission): void
    {
        if (!$this->hasPermission($permission->name)) {
            $this->permissions()->attach($permission->id);
        }
    }

    public function removePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }

    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }
}
