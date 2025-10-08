<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SunatLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'operation',
        'endpoint',
        'request_payload',
        'response_payload',
        'status_code',
        'is_success',
        'executed_at',
    ];

    protected $casts = [
        'is_success' => 'boolean',
        'executed_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
