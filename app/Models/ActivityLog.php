<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'event',
        'batch_uuid',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function causer()
    {
        return $this->morphTo('causer', 'causer_type', 'causer_id');
    }

    public function subject()
    {
        return $this->morphTo('subject', 'subject_type', 'subject_id');
    }

    // Accessors
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('M d, Y h:i A');
    }

    public function getActionBadgeAttribute(): string
    {
        $badgeClass = '';
        switch ($this->event) {
            case 'user_created':
            case 'batch_created':
            case 'transaction_created':
                $badgeClass = 'bg-success';
                break;
            case 'user_login':
            case 'token_generated':
                $badgeClass = 'bg-info';
                break;
            case 'password_changed':
            case 'batch_status_changed':
            case 'transaction_status_changed':
                $badgeClass = 'bg-warning';
                break;
            case 'error':
            case 'user_deleted':
                $badgeClass = 'bg-danger';
                break;
            default:
                $badgeClass = 'bg-secondary';
                break;
        }
        return "<span class='badge {$badgeClass}'>" . ucfirst(str_replace('_', ' ', $this->event ?? 'unknown')) . "</span>";
    }

    // Scopes
    public function scopeByUser($query, int $userId)
    {
        return $query->where('causer_id', $userId)
                    ->where('causer_type', User::class);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('event', $action);
    }

    public function scopeByModel($query, string $modelType)
    {
        return $query->where('subject_type', $modelType);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }
}
