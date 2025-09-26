<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Recipient extends Model
{
    protected $fillable = [
        'batch_upload_id',
        'name',
        'customer_name',
        'address',
        'phone_number',
        'disco',
        'meter_number',
        'meter_type',
        'amount',
        'status',
        'transaction_reference',
        'error_message',
        'processed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function batchUpload(): BelongsTo
    {
        return $this->belongsTo(BatchUpload::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }
}
