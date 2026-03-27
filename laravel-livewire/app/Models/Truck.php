<?php

namespace App\Models;

use App\Enums\Fleet\AssignmentStatus;
use App\Enums\Fleet\MaintenanceStatus;
use App\Enums\Fleet\TruckStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

class Truck extends Model
{
    use HasFactory;

    /**
     * last_maintenance y next_maintenance son un SNAPSHOT/CACHE derivado del historial
     * de Maintenance (fuente de verdad). Se mantienen para dashboards/reportes y
     * filtros rápidos.
     */
    protected $attributes = [
        'status' => TruckStatus::Available->value,
    ];

    protected $fillable = [
        'plate_number',
        'brand',
        'model',
        'year',
        'type',
        'tuce_number',
        'special_auth_issuer',
        'special_auth_number',
        'capacity',
        'is_secondary',
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
        'status' => TruckStatus::class,
    ];

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function completedMaintenances(): HasMany
    {
        return $this->maintenances()->where('status', MaintenanceStatus::Completed->value);
    }

    public function pendingMaintenances(): HasMany
    {
        return $this->maintenances()->whereIn('status', [
            MaintenanceStatus::Scheduled->value,
            MaintenanceStatus::InProgress->value,
        ]);
    }

    public function lastCompletedMaintenanceDate(): ?Carbon
    {
        $maintenance = $this->completedMaintenances()
            ->orderByDesc('maintenance_date')
            ->orderByDesc('id')
            ->first();

        return $maintenance?->maintenance_date ? Carbon::parse($maintenance->maintenance_date)->startOfDay() : null;
    }

    public function nextScheduledMaintenanceDate(): ?Carbon
    {
        $maintenance = $this->maintenances()
            ->where('status', MaintenanceStatus::Scheduled->value)
            ->whereDate('maintenance_date', '>=', Carbon::today())
            ->orderBy('maintenance_date')
            ->orderBy('id')
            ->first();

        return $maintenance?->maintenance_date ? Carbon::parse($maintenance->maintenance_date)->startOfDay() : null;
    }

    public function getLastMaintenanceDerivedAttribute(): ?Carbon
    {
        return $this->lastCompletedMaintenanceDate() ?? $this->last_maintenance;
    }

    public function getNextMaintenanceDerivedAttribute(): ?Carbon
    {
        $scheduled = $this->nextScheduledMaintenanceDate();

        $last = $this->lastCompletedMaintenanceDate() ?? $this->last_maintenance;
        $intervalDays = $this->maintenance_interval_days ? max((int) $this->maintenance_interval_days, 1) : null;
        $byInterval = ($last && $intervalDays) ? $last->copy()->addDays($intervalDays) : null;

        if (! $scheduled) {
            return $byInterval ?? $this->next_maintenance;
        }

        if (! $byInterval) {
            return $scheduled;
        }

        return $byInterval->lessThanOrEqualTo($scheduled) ? $byInterval : $scheduled;
    }

    public function maintenanceNextSource(): string
    {
        if ($this->nextScheduledMaintenanceDate()) {
            return 'scheduled';
        }

        $last = $this->lastCompletedMaintenanceDate() ?? $this->last_maintenance;
        $intervalDays = max((int) ($this->maintenance_interval_days ?? 0), 0);

        if ($last && $intervalDays > 0) {
            return 'policy';
        }

        return 'none';
    }

    public function maintenanceNextSourceLabel(): string
    {
        return match ($this->maintenanceNextSource()) {
            'scheduled' => 'Programado',
            'policy' => 'Por política',
            default => 'No programado',
        };
    }

    public function maintenanceNextSourceBadgeClasses(): string
    {
        return match ($this->maintenanceNextSource()) {
            'scheduled' => 'bg-success-soft text-success-strong ',
            'policy' => 'bg-warning-soft text-warning ',
            default => 'bg-surface-muted text-token ',
        };
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
        return $query->whereNotIn('status', [
            TruckStatus::Maintenance->value,
            TruckStatus::OutOfService->value,
        ]);
    }

    public function hasActiveAssignments(): bool
    {
        return $this->assignments()
            ->whereNotIn('status', [AssignmentStatus::Completed->value, AssignmentStatus::Cancelled->value])
            ->exists();
    }

    public function requiresMaintenanceAlert(?\DateTimeInterface $referenceDate = null): bool
    {
        $referenceDate = $referenceDate ? Carbon::instance($referenceDate) : now();

        $dueByDate = $this->next_maintenance_derived
            ?? ($this->last_maintenance_derived && $this->maintenance_interval_days
                ? $this->last_maintenance_derived->copy()->addDays((int) $this->maintenance_interval_days)
                : null);

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

        $dueByDate = $this->next_maintenance_derived
            ?? ($this->last_maintenance_derived && $this->maintenance_interval_days
                ? $this->last_maintenance_derived->copy()->addDays((int) $this->maintenance_interval_days)
                : null);

        if ($dueByDate && $dueByDate->diffInDays($referenceDate, false) >= -15) {
            return 'warning';
        }

        return 'ok';
    }
}
