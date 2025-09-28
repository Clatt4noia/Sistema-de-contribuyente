<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'last_name',
        'document_number',
        'license_number',
        'license_expiration',
        'phone',
        'email',
        'address',
        'status',
        'work_schedule',
        'notes',
    ];

    protected $casts = [
        'license_expiration' => 'date',
        'work_schedule' => 'array',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DriverSchedule::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(DriverEvaluation::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->name} {$this->last_name}";
    }
}
