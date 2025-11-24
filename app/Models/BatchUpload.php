<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchUpload extends Model
{
    protected $fillable = [
        'filename',
        'batch_name',
        'batch_type',
        'total_recipients',
        'processed_recipients',
        'successful_transactions',
        'failed_transactions',
        'total_amount',
        'status',
        'notes',
        'sms_template',
        'email_template',
        'enable_sms',
        'enable_email',
        'user_id'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'enable_sms' => 'boolean',
        'enable_email' => 'boolean',
    ];

    public function recipients(): HasMany
    {
        return $this->hasMany(Recipient::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCompletionPercentageAttribute(): float
    {
        if ($this->total_recipients == 0) return 0;
        return round(($this->processed_recipients / $this->total_recipients) * 100, 2);
    }

    public function getSuccessRateAttribute(): float
    {
        if ($this->processed_recipients == 0) return 0;
        return round(($this->successful_transactions / $this->processed_recipients) * 100, 2);
    }
}
