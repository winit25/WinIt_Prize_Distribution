<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MobileTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'amount',
        'status', // e.g., pending, successful, failed
        'transaction_type', // e.g., airtime, data, bill_payment
        'reference', // Internal reference
        'provider', // e.g., MTN, Glo, DSTV
        'provider_reference', // Reference from the provider
        'response_data', // Store full API response
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'response_data' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($transaction) {
            if (empty($transaction->reference)) {
                $transaction->reference = 'MT-' . strtoupper(uniqid());
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return '₦' . number_format($this->amount, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        $badgeClass = '';
        switch ($this->status) {
            case 'successful':
                $badgeClass = 'bg-success';
                break;
            case 'failed':
                $badgeClass = 'bg-danger';
                break;
            case 'pending':
                $badgeClass = 'bg-warning';
                break;
            default:
                $badgeClass = 'bg-secondary';
                break;
        }
        return "<span class='badge {$badgeClass}'>" . ucfirst($this->status) . "</span>";
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'successful');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }
}
