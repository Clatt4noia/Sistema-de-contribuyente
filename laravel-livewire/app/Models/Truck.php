<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Truck extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'brand',
        'model',
        'year',
        'type',
        'capacity',
        'mileage',
        'status',
        'last_maintenance',
        'next_maintenance',
        'technical_details',
    ];

    protected $casts = [
        'last_maintenance' => 'date',
        'next_maintenance' => 'date',
        'mileage' => 'integer',
    ];

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
