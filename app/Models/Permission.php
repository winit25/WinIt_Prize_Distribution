<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permission');
    }

    // Accessors
    public function getStatusBadgeAttribute(): string
    {
        $badgeClass = $this->is_active ? 'bg-success' : 'bg-danger';
        $status = $this->is_active ? 'Active' : 'Inactive';
        return "<span class='badge {$badgeClass}'>" . $status . "</span>";
    }

    public function getCategoryBadgeAttribute(): string
    {
        $badgeClass = '';
        switch ($this->category) {
            case 'user_management':
                $badgeClass = 'bg-primary';
                break;
            case 'batch_management':
                $badgeClass = 'bg-info';
                break;
            case 'transaction_management':
                $badgeClass = 'bg-success';
                break;
            case 'system_administration':
                $badgeClass = 'bg-warning';
                break;
            default:
                $badgeClass = 'bg-secondary';
                break;
        }
        return "<span class='badge {$badgeClass}'>" . ucfirst(str_replace('_', ' ', $this->category)) . "</span>";
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

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // Helper methods
    public function isAssignedToRole(Role $role): bool
    {
        return $this->roles()->where('role_id', $role->id)->exists();
    }

    public function isAssignedToUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }
}
