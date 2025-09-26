<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatchUpload extends Model
{
    protected $fillable = [
        'filename',
        'batch_name',
        'total_recipients',
        'processed_recipients',
        'successful_transactions',
        'failed_transactions',
        'total_amount',
        'status',
        'notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function recipients(): HasMany
    {
        return $this->hasMany(Recipient::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
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
