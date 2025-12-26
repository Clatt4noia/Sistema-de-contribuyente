<?php

namespace App\Models;

use App\Enums\Fleet\AssignmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    protected $attributes = [
        'status' => AssignmentStatus::Scheduled->value,
    ];

    protected $fillable = [
        'truck_id',
        'driver_id',
        'order_id',
        'start_date',
        'end_date',
        'status',
        'description',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'status' => AssignmentStatus::class,
    ];

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(RouteIncident::class);
    }

    public function locationUpdates(): HasMany
    {
        return $this->hasMany(VehicleLocationUpdate::class);
    }
}
