<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'planner',
        'route_summary',
        'map_url',
        'route_data',
    ];

    protected $casts = [
        'route_data' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
