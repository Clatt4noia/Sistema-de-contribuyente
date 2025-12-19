<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

class Truck extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'brand',
        'model',
        'year',
        'type',
        'mtc_registration_number',
        'capacity',
        'mileage',
        'status',
        'last_maintenance',
        'next_maintenance',
        'technical_details',
        'maintenance_interval_days',
        'maintenance_mileage_threshold',
        'last_maintenance_mileage',
    ];

    protected $casts = [
        'last_maintenance' => 'date',
        'next_maintenance' => 'date',
        'mileage' => 'integer',
        'maintenance_interval_days' => 'integer',
        'maintenance_mileage_threshold' => 'integer',
        'last_maintenance_mileage' => 'integer',
    ];

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function cargoTypes(): BelongsToMany
    {
        return $this->belongsToMany(CargoType::class)->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable')->orderBy('expires_at');
    }

    public function scopeOperational($query)
    {
        return $query->whereNotIn('status', ['maintenance', 'out_of_service']);
    }

    public function requiresMaintenanceAlert(?\DateTimeInterface $referenceDate = null): bool
    {
        $referenceDate = $referenceDate ? Carbon::instance($referenceDate) : now();

        $dueByDate = $this->last_maintenance && $this->maintenance_interval_days
            ? $this->last_maintenance->copy()->addDays($this->maintenance_interval_days)
            : null;

        $dueByMileage = $this->last_maintenance_mileage && $this->maintenance_mileage_threshold
            ? ($this->mileage - $this->last_maintenance_mileage) >= $this->maintenance_mileage_threshold
            : false;

        if ($dueByDate && $dueByDate->lessThanOrEqualTo($referenceDate)) {
            return true;
        }

        return $dueByMileage;
    }

    public function maintenanceAlertLevel(?\DateTimeInterface $referenceDate = null): string
    {
        if ($this->requiresMaintenanceAlert($referenceDate)) {
            return 'danger';
        }

        $referenceDate = $referenceDate ? Carbon::instance($referenceDate) : now();

        $dueByDate = $this->last_maintenance && $this->maintenance_interval_days
            ? $this->last_maintenance->copy()->addDays($this->maintenance_interval_days)
            : null;

        if ($dueByDate && $dueByDate->diffInDays($referenceDate, false) >= -15) {
            return 'warning';
        }

        return 'ok';
    }
}
