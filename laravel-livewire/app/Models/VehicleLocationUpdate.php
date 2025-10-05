<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleLocationUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'truck_id',
        'latitude',
        'longitude',
        'speed_kph',
        'reported_at',
        'status',
        'raw_payload',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'speed_kph' => 'float',
        'reported_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }
}
