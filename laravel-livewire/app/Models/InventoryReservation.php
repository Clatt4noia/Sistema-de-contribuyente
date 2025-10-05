<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_sku',
        'quantity',
        'status',
        'reserved_at',
        'released_at',
        'source_system',
        'payload',
    ];

    protected $casts = [
        'quantity' => 'float',
        'reserved_at' => 'datetime',
        'released_at' => 'datetime',
        'payload' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
