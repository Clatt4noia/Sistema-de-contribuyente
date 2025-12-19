<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'destinatario_document_type',
        'destinatario_document_number',
        'destinatario_name',
        'cargo_type_id',
        'reference',
        'origin',
        'origin_ubigeo',
        'origin_address',
        'origin_latitude',
        'origin_longitude',
        'destination',
        'destination_ubigeo',
        'destination_address',
        'destination_latitude',
        'destination_longitude',
        'pickup_date',
        'delivery_date',
        'delivery_window_start',
        'delivery_window_end',
        'status',
        'cargo_details',
        'cargo_weight_kg',
        'cargo_volume_m3',
        'total_packages',
        'estimated_distance_km',
        'estimated_duration_hours',
        'estimated_cost',
        'cost_breakdown',
        'notes',
        'estimated_cost',
        'referential_rate_sxtm',
        'referential_route_key',
        'referential_route_dest',
        'referential_source',
        'referential_year',
    ];

    protected $casts = [
        'pickup_date' => 'datetime',
        'delivery_date' => 'datetime',
        'delivery_window_start' => 'datetime',
        'delivery_window_end' => 'datetime',
        'estimated_distance_km' => 'float',
        'estimated_duration_hours' => 'float',
        'cargo_weight_kg' => 'float',
        'cargo_volume_m3' => 'float',
        'total_packages' => 'integer',
        'estimated_cost' => 'float',
        'origin_latitude' => 'float',
        'origin_longitude' => 'float',
        'destination_latitude' => 'float',
        'destination_longitude' => 'float',
        'cost_breakdown' => 'array',
        'estimated_cost' => 'decimal:2',
        'referential_rate_sxtm' => 'decimal:2',
        'referential_year' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function cargoType(): BelongsTo
    {
        return $this->belongsTo(CargoType::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(Assignment::class)->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function routePlans(): HasMany
    {
        return $this->hasMany(RoutePlan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // Aquí está la corrección: cambio de `HasMany` a `HasManyThrough`
    public function incidents(): HasManyThrough
    {
        return $this->hasManyThrough(RouteIncident::class, Assignment::class);
    }

    // Aquí también: `HasManyThrough` en lugar de `HasMany`
    public function locationUpdates(): HasManyThrough
    {
        return $this->hasManyThrough(VehicleLocationUpdate::class, Assignment::class);
    }

    public function inventoryReservations(): HasMany
    {
        return $this->hasMany(InventoryReservation::class);
    }
}
