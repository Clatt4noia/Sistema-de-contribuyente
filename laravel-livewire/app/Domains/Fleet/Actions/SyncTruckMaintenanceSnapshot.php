<?php

namespace App\Domains\Fleet\Actions;

use App\Enums\Fleet\AssignmentStatus;
use App\Enums\Fleet\MaintenanceStatus;
use App\Enums\Fleet\TruckStatus;
use App\Models\Truck;
use Illuminate\Support\Carbon;

class SyncTruckMaintenanceSnapshot
{
    /**
     * Sincroniza columnas "cache" del camión (last_maintenance/next_maintenance y
     * derivados) usando el historial de maintenances como fuente de verdad.
     *
     * Política para next_maintenance:
     * - Se calcula "por intervalo" usando la última fecha completada + maintenance_interval_days (default 90).
     * - Se compara contra el mantenimiento Scheduled futuro más próximo (si existe) y se toma el menor
     *   entre ambos para reflejar la fecha más próxima relevante (agenda vs vencimiento).
     */
    public function execute(Truck $truck): Truck
    {
        $truck->refresh();

        $originalTruckStatus = $truck->status;

        $lastCompleted = $truck->maintenances()
            ->where('status', MaintenanceStatus::Completed->value)
            ->orderByDesc('maintenance_date')
            ->orderByDesc('id')
            ->first();

        $lastCompletedDate = $lastCompleted?->maintenance_date
            ? Carbon::parse($lastCompleted->maintenance_date)->startOfDay()
            : null;

        $intervalDays = max((int) ($truck->maintenance_interval_days ?? 90), 1);
        $nextByInterval = $lastCompletedDate?->copy()->addDays($intervalDays);

        $nextScheduled = $truck->maintenances()
            ->where('status', MaintenanceStatus::Scheduled->value)
            ->whereDate('maintenance_date', '>=', Carbon::today())
            ->orderBy('maintenance_date')
            ->orderBy('id')
            ->first();

        $nextScheduledDate = $nextScheduled?->maintenance_date
            ? Carbon::parse($nextScheduled->maintenance_date)->startOfDay()
            : null;

        $nextDerived = $this->minDate($nextByInterval, $nextScheduledDate);

        $truck->last_maintenance = $lastCompletedDate;
        $truck->next_maintenance = $nextDerived;

        if (! is_null($lastCompleted?->odometer)) {
            $truck->last_maintenance_mileage = (int) $lastCompleted->odometer;
            $truck->mileage = max((int) ($truck->mileage ?? 0), (int) $lastCompleted->odometer);
        }

        $truck->status = $this->resolveTruckStatus($truck, $originalTruckStatus);

        $dirtyFields = [
            'status',
            'last_maintenance',
            'next_maintenance',
            'mileage',
            'last_maintenance_mileage',
        ];

        if ($truck->isDirty($dirtyFields)) {
            if (method_exists($truck, 'saveQuietly')) {
                $truck->saveQuietly();
            } else {
                Truck::withoutEvents(fn () => $truck->save());
            }
        }

        return $truck;
    }

    protected function resolveTruckStatus(Truck $truck, TruckStatus|string|null $originalStatus): TruckStatus
    {
        $originalStatusValue = $originalStatus instanceof TruckStatus ? $originalStatus->value : $originalStatus;

        if ($originalStatusValue === TruckStatus::OutOfService->value) {
            return TruckStatus::OutOfService;
        }

        $hasInProgressMaintenance = $truck->maintenances()
            ->where('status', MaintenanceStatus::InProgress->value)
            ->exists();

        if ($hasInProgressMaintenance) {
            return TruckStatus::Maintenance;
        }

        $hasDueScheduledMaintenance = $truck->maintenances()
            ->where('status', MaintenanceStatus::Scheduled->value)
            ->whereDate('maintenance_date', '<=', Carbon::today())
            ->exists();

        if ($hasDueScheduledMaintenance) {
            return TruckStatus::Maintenance;
        }

        $hasActiveAssignments = $truck->assignments()
            ->whereNotIn('status', [AssignmentStatus::Completed->value, AssignmentStatus::Cancelled->value])
            ->exists();

        if ($hasActiveAssignments) {
            return TruckStatus::InUse;
        }

        return TruckStatus::Available;
    }

    protected function minDate(?Carbon $a, ?Carbon $b): ?Carbon
    {
        if (! $a) {
            return $b;
        }

        if (! $b) {
            return $a;
        }

        return $a->lessThanOrEqualTo($b) ? $a : $b;
    }
}
