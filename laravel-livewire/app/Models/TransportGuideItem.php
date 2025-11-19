<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportGuideItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transport_guide_id',
        'description',
        'unit_of_measure',
        'quantity',
        'weight',
        'order_item_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'weight' => 'decimal:3',
    ];

    public function transportGuide(): BelongsTo
    {
        return $this->belongsTo(TransportGuide::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_item_id');
    }
}
