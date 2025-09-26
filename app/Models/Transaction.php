<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'recipient_id',
        'batch_upload_id',
        'buypower_reference',
        'order_id',
        'phone_number',
        'amount',
        'status',
        'api_response',
        'token',
        'units',
        'error_message',
        'processed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'api_response' => 'array',
        'processed_at' => 'datetime',
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class);
    }

    public function batchUpload(): BelongsTo
    {
        return $this->belongsTo(BatchUpload::class);
    }
}
