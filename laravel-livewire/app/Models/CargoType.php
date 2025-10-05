<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CargoType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'requires_refrigeration',
        'is_hazardous',
    ];

    protected $casts = [
        'requires_refrigeration' => 'boolean',
        'is_hazardous' => 'boolean',
    ];

    public function trucks(): BelongsToMany
    {
        return $this->belongsToMany(Truck::class)->withTimestamps();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
