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
        'action',
        'event',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'ip_address',
        'user_agent',
        'user_id', // For backward compatibility
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

    /**
     * Parse user agent to extract device information
     */
    public function getDeviceInfoAttribute(): array
    {
        $userAgent = $this->user_agent ?? '';
        
        if (empty($userAgent)) {
            return [
                'device_type' => 'Unknown',
                'browser' => 'Unknown',
                'os' => 'Unknown',
                'display' => 'Unknown'
            ];
        }

        // Detect device type
        $deviceType = 'Desktop';
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|Windows Phone/i', $userAgent)) {
            if (preg_match('/iPad/i', $userAgent)) {
                $deviceType = 'Tablet';
            } elseif (preg_match('/Mobile|Android|iPhone|iPod|BlackBerry|Windows Phone/i', $userAgent)) {
                $deviceType = 'Mobile';
            }
        }

        // Detect browser
        $browser = 'Unknown';
        if (preg_match('/Chrome/i', $userAgent) && !preg_match('/Edg|OPR/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari/i', $userAgent) && !preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edg/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/OPR/i', $userAgent)) {
            $browser = 'Opera';
        } elseif (preg_match('/MSIE|Trident/i', $userAgent)) {
            $browser = 'Internet Explorer';
        }

        // Detect OS
        $os = 'Unknown';
        if (preg_match('/Windows NT/i', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Mac OS X/i', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $os = 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            $os = 'iOS';
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'os' => $os,
            'display' => "{$deviceType} - {$browser} on {$os}"
        ];
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
    
    public function scopeByCauser($query, $causer)
    {
        return $query->where('causer_type', get_class($causer))
                    ->where('causer_id', $causer->id);
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
