<?php

namespace App\Services\Fleet;

use App\Models\Assignment;
use App\Models\Driver;
use App\Models\DriverTraining;
use App\Models\Order;
use App\Models\Truck;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignmentService
{
    public function save(Assignment $assignment, array $data): Assignment
    {
        $data['description'] = trim((string) $data['description']);
        $data['notes'] = trim((string) $data['notes']) ?: null;

        $start = Carbon::parse($data['start_date']);
        $end = $data['end_date'] ? Carbon::parse($data['end_date']) : $start->copy();

        $this->validateDates($start, $end);
        $driver = Driver::with('trainings')->findOrFail($data['driver_id']);
        $truck = Truck::with('maintenances')->findOrFail($data['truck_id']);

        $this->validateAvailability($assignment, $driver, $truck, $start, $end);

        DB::transaction(function () use ($assignment, $data, $start, $end) {
            $originalTruck = $assignment->getOriginal('truck_id');
            $originalDriver = $assignment->getOriginal('driver_id');
            $originalStatus = $assignment->getOriginal('status');

            $assignment->fill([
                'order_id' => $data['order_id'],
                'truck_id' => $data['truck_id'],
                'driver_id' => $data['driver_id'],
                'status' => $data['status'],
                'description' => $data['description'],
                'notes' => $data['notes'],
            ]);

            $assignment->start_date = $start;
            $assignment->end_date = $data['end_date'] ? $end : null;
            $assignment->save();

            $this->syncTruckAvailability($assignment, $originalTruck);
            $this->syncDriverAvailability($assignment, $originalDriver);
            $this->syncOrderStatus($assignment, $originalStatus);
        });

        return $assignment;
    }

    public function findAvailableTruck(Assignment $assignment, Carbon $start, Carbon $end): ?Truck
    {
        return Truck::operational()
            ->with('maintenances')
            ->orderBy('next_maintenance')
            ->get()
            ->first(function (Truck $truck) use ($assignment, $start, $end) {
                if ($assignment->truck_id === $truck->id) {
                    return true;
                }

                if ($truck->requiresMaintenanceAlert($start)) {
                    return false;
                }

                if ($this->resourceOccupied('truck_id', $truck->id, $start, $end, $assignment->id)) {
                    return false;
                }

                $hasPendingMaintenance = $truck->maintenances
                    ->filter(fn ($maintenance) => in_array($maintenance->status, ['scheduled', 'in_progress'], true))
                    ->first(fn ($maintenance) => $maintenance->maintenance_date && $maintenance->maintenance_date->between($start->copy()->startOfDay(), $end->copy()->endOfDay()));

                return ! $hasPendingMaintenance;
            });
    }

    public function findAvailableDriver(Assignment $assignment, Carbon $start, Carbon $end): ?Driver
    {
        return Driver::query()
            ->with('trainings')
            ->whereIn('status', ['active'])
            ->orderBy('license_expiration')
            ->get()
            ->first(function (Driver $driver) use ($assignment, $start, $end) {
                if (! $driver->hasValidLicenseAt($start)) {
                    return false;
                }

                if (! $driver->isAvailableBetween($start, $end, $assignment->id)) {
                    return false;
                }

                return $driver->trainings->contains(fn (DriverTraining $training) => ! $training->expires_at || $training->expires_at->greaterThanOrEqualTo($start));
            });
    }

    public function resourceOccupied(string $column, int $id, Carbon $start, Carbon $end, ?int $ignoreAssignmentId = null): bool
    {
        return Assignment::where($column, $id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->when($ignoreAssignmentId, fn ($query) => $query->where('id', '!=', $ignoreAssignmentId))
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_date', '<=', $start)->where(function ($inner) use ($end) {
                            $inner->whereNull('end_date')->orWhere('end_date', '>=', $end);
                        });
                    })
                    ->orWhereBetween('end_date', [$start, $end]);
            })
            ->exists();
    }

    protected function validateAvailability(Assignment $assignment, Driver $driver, Truck $truck, Carbon $start, Carbon $end): void
    {
        if ($this->resourceOccupied('truck_id', (int) $truck->id, $start, $end, $assignment->id)) {
            throw ValidationException::withMessages([
                'form.truck_id' => 'El camion seleccionado ya esta asignado en esas fechas.',
            ]);
        }

        if ($this->resourceOccupied('driver_id', (int) $driver->id, $start, $end, $assignment->id)) {
            throw ValidationException::withMessages([
                'form.driver_id' => 'El chofer seleccionado ya esta asignado en esas fechas.',
            ]);
        }

        if (! $driver->hasValidLicenseAt($start)) {
            throw ValidationException::withMessages([
                'form.driver_id' => 'La licencia del chofer esta vencida para la fecha seleccionada.',
            ]);
        }

        if (! $driver->isAvailableBetween($start, $end, $assignment->id)) {
            throw ValidationException::withMessages([
                'form.driver_id' => 'El chofer no esta disponible en el rango seleccionado.',
            ]);
        }

        $hasValidTraining = $driver->trainings->first(function (DriverTraining $training) use ($start) {
            return ! $training->expires_at || $training->expires_at->greaterThanOrEqualTo($start);
        });

        if (! $hasValidTraining) {
            throw ValidationException::withMessages([
                'form.driver_id' => 'El chofer no cuenta con capacitaciones vigentes.',
            ]);
        }

        if (in_array($truck->status, ['maintenance', 'out_of_service'], true)) {
            throw ValidationException::withMessages([
                'form.truck_id' => 'El camion seleccionado no esta disponible (mantenimiento o fuera de servicio).',
            ]);
        }

        if ($truck->requiresMaintenanceAlert($start)) {
            throw ValidationException::withMessages([
                'form.truck_id' => 'El camion requiere mantenimiento antes de la fecha seleccionada.',
            ]);
        }

        $pendingMaintenance = $truck->maintenances
            ->filter(fn ($maintenance) => in_array($maintenance->status, ['scheduled', 'in_progress'], true))
            ->first(fn ($maintenance) => $maintenance->maintenance_date && $maintenance->maintenance_date->between($start->copy()->startOfDay(), $end->copy()->endOfDay()));

        if ($pendingMaintenance) {
            throw ValidationException::withMessages([
                'form.truck_id' => 'El camion tiene un mantenimiento programado en el periodo seleccionado.',
            ]);
        }
    }

    protected function validateDates(Carbon $start, Carbon $end): void
    {
        if ($start->greaterThan($end)) {
            throw ValidationException::withMessages([
                'form.end_date' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
            ]);
        }
    }

    protected function syncTruckAvailability(Assignment $assignment, ?int $originalTruck): void
    {
        if ($originalTruck && $originalTruck !== (int) $assignment->truck_id) {
            $this->releaseTruck($originalTruck);
        }

        $this->applyTruckStatus((int) $assignment->truck_id, $assignment->status);
    }

    protected function syncDriverAvailability(Assignment $assignment, ?int $originalDriver): void
    {
        if ($originalDriver && $originalDriver !== (int) $assignment->driver_id) {
            $this->releaseDriver($originalDriver);
        }

        $this->applyDriverStatus((int) $assignment->driver_id, $assignment->status);
    }

    protected function syncOrderStatus(Assignment $assignment, ?string $originalStatus): void
    {
        $order = $assignment->order;

        if (!$order) {
            return;
        }

        if (in_array($assignment->status, ['scheduled', 'in_progress'], true)) {
            $order->status = 'en_route';
        }

        if ($assignment->status === 'completed') {
            $order->status = 'delivered';
            $order->delivery_date = $assignment->end_date ?? now();
        }

        if ($assignment->status === 'cancelled') {
            $order->status = 'cancelled';
        }

        if ($originalStatus === 'cancelled' && $assignment->status === 'scheduled') {
            $order->status = 'pending';
        }

        $order->save();
    }

    protected function applyTruckStatus(int $truckId, string $assignmentStatus): void
    {
        $truck = Truck::find($truckId);
        if (!$truck) {
            return;
        }

        if (in_array($assignmentStatus, ['scheduled', 'in_progress'], true)) {
            $truck->status = 'in_use';
            $truck->save();
            return;
        }

        if (in_array($assignmentStatus, ['completed', 'cancelled'], true)) {
            $this->releaseTruck($truckId);
        }
    }

    protected function releaseTruck(int $truckId): void
    {
        $truck = Truck::find($truckId);
        if (!$truck) {
            return;
        }

        $hasOtherAssignments = Assignment::query()
            ->where('truck_id', $truckId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if (!$hasOtherAssignments) {
            $truck->status = 'available';
            $truck->save();
        }
    }

    protected function applyDriverStatus(int $driverId, string $assignmentStatus): void
    {
        $driver = Driver::find($driverId);
        if (!$driver) {
            return;
        }

        if (in_array($assignmentStatus, ['scheduled', 'in_progress'], true)) {
            $driver->status = 'assigned';
            $driver->save();
            return;
        }

        if (in_array($assignmentStatus, ['completed', 'cancelled'], true)) {
            $this->releaseDriver($driverId);
        }
    }

    protected function releaseDriver(int $driverId): void
    {
        $driver = Driver::find($driverId);
        if (!$driver) {
            return;
        }

        $hasOtherAssignments = Assignment::query()
            ->where('driver_id', $driverId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if (!$hasOtherAssignments) {
            $driver->status = 'active';
            $driver->save();
        }
    }
}
