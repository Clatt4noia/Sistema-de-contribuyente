<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'last_name',
        'document_type',
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

    protected function status(): Attribute
    {
        return Attribute::make(
            get: static function (?string $value) {
                if ($value === null) {
                    return null;
                }

                $normalized = strtolower(trim($value));

                return match ($normalized) {
                    'active', 'activo', 'available' => 'active',
                    'assigned', 'asignado' => 'assigned',
                    'inactive', 'inactivo', 'baja', 'desactivado' => 'inactive',
                    'on_leave', 'permiso', 'de permiso', 'leave' => 'on_leave',
                    default => $normalized,
                };
            },
            set: static function (?string $value) {
                if ($value === null) {
                    return null;
                }

                $normalized = strtolower(trim($value));

                return match ($normalized) {
                    'activo', 'available' => 'active',
                    'asignado' => 'assigned',
                    'inactivo', 'baja', 'desactivado' => 'inactive',
                    'permiso', 'de permiso', 'leave' => 'on_leave',
                    default => $normalized,
                };
            }
        );
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(DriverTraining::class);
    }

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

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable')->orderBy('expires_at');
    }

    public function hasValidLicenseAt($date): bool
    {
        $date = $date instanceof \DateTimeInterface ? $date : Carbon::parse($date);

        return $this->license_expiration?->endOfDay()->greaterThanOrEqualTo($date);
    }

    public function isAvailableBetween($start, $end, ?int $ignoreAssignmentId = null): bool
    {
        $start = $start instanceof \DateTimeInterface ? $start : Carbon::parse($start);
        $end = $end instanceof \DateTimeInterface ? $end : Carbon::parse($end);

        $hasOverlap = $this->assignments()
            ->when($ignoreAssignmentId, fn ($query) => $query->where('id', '!=', $ignoreAssignmentId))
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($overlap) use ($start, $end) {
                        $overlap->where('start_date', '<=', $start)->where('end_date', '>=', $end);
                    });
            })
            ->exists();

        return ! $hasOverlap;
    }

    public function activeTrainings()
    {
        return $this->trainings()->where(function ($query) {
            $query->whereNull('expires_at')->orWhereDate('expires_at', '>=', now());
        });
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->name} {$this->last_name}";
    }
}
