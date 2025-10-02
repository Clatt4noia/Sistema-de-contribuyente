<?php

namespace App\Livewire\Fleet;

use App\Models\Assignment;
use App\Models\Driver;
use App\Models\DriverTraining;
use App\Models\Order;
use App\Models\Truck;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app.sidebar', ['title' => 'Asignacion'])]
#[Title('Asignacion')]
class AssignmentForm extends Component
{
    use AuthorizesRequests;

    public Assignment $assignment;
    public bool $isEdit = false;
    public array $form = [];
    public $trucks = [];
    public $drivers = [];
    public $orders = [];
    public ?Order $orderPreview = null;
    public string $mode = 'manual';
    public ?string $autoAssignAlert = null;

    protected function rules(): array
    {
        return [
            'form.order_id' => 'required|exists:orders,id',
            'form.truck_id' => 'required|exists:trucks,id',
            'form.driver_id' => 'required|exists:drivers,id',
            'form.start_date' => 'required|date',
            'form.end_date' => 'nullable|date|after_or_equal:form.start_date',
            'form.status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'form.description' => 'required|string|max:255',
            'form.notes' => 'nullable|string',
            'mode' => 'required|in:manual,automatic',
        ];
    }

    public function mount($id = null): void
    {
        if ($id) {
            $this->assignment = Assignment::findOrFail($id);
            $this->authorize('update', $this->assignment);
            $this->isEdit = true;

            if ($this->assignment->start_date) {
                $this->assignment->start_date = $this->assignment->start_date->format('Y-m-d\TH:i');
            }

            if ($this->assignment->end_date) {
                $this->assignment->end_date = $this->assignment->end_date->format('Y-m-d\TH:i');
            }
        } else {
            $this->authorize('create', Assignment::class);
            $this->assignment = new Assignment([
                'status' => 'scheduled',
                'start_date' => now()->format('Y-m-d\TH:i'),
            ]);
        }

        $this->form = [
            'order_id' => $this->assignment->order_id ?? '',
            'truck_id' => $this->assignment->truck_id ?? '',
            'driver_id' => $this->assignment->driver_id ?? '',
            'start_date' => $this->assignment->start_date instanceof Carbon
                ? $this->assignment->start_date->format('Y-m-d\TH:i')
                : ($this->assignment->start_date ?? now()->format('Y-m-d\TH:i')),
            'end_date' => $this->assignment->end_date instanceof Carbon
                ? $this->assignment->end_date->format('Y-m-d\TH:i')
                : ($this->assignment->end_date ?? ''),
            'status' => $this->assignment->status ?? 'scheduled',
            'description' => $this->assignment->description ?? '',
            'notes' => $this->assignment->notes ?? '',
        ];

        $this->loadOptions();
    }

    public function updatedMode(): void
    {
        $this->autoAssignAlert = null;

        if ($this->mode === 'automatic') {
            $this->autoAssignResources();
        }
    }

    public function updatedFormOrderId(): void
    {
        $this->loadOptions();
    }

    public function updatedFormStatus(): void
    {
        $this->loadOptions();
    }

    public function save()
    {
        $this->authorize(
            $this->isEdit ? 'update' : 'create',
            $this->isEdit ? $this->assignment : Assignment::class
        );

        if ($this->mode === 'automatic' && empty($this->form['truck_id']) && empty($this->form['driver_id'])) {
            $this->autoAssignResources();

            if (empty($this->form['truck_id']) || empty($this->form['driver_id'])) {
                $this->addError('mode', 'No se encontraron recursos disponibles para asignacion automatica.');
                return;
            }
        }

        $validated = $this->validate();
        $data = $validated['form'];

        $data['description'] = trim((string) $data['description']);
        $data['notes'] = trim((string) $data['notes']) ?: null;

        $start = Carbon::parse($data['start_date']);
        $end = $data['end_date'] ? Carbon::parse($data['end_date']) : $start->copy();

        if ($start->greaterThan($end)) {
            $this->addError('form.end_date', 'La fecha de fin debe ser posterior o igual a la fecha de inicio.');
            return;
        }

        $driver = Driver::with('trainings')->findOrFail($data['driver_id']);
        $truck = Truck::with('maintenances')->findOrFail($data['truck_id']);

        if ($this->resourceOccupied('truck_id', (int) $data['truck_id'], $start, $end)) {
            $this->addError('form.truck_id', 'El camion seleccionado ya esta asignado en esas fechas.');
            return;
        }

        if ($this->resourceOccupied('driver_id', (int) $data['driver_id'], $start, $end)) {
            $this->addError('form.driver_id', 'El chofer seleccionado ya esta asignado en esas fechas.');
            return;
        }

        if (! $driver->hasValidLicenseAt($start)) {
            $this->addError('form.driver_id', 'La licencia del chofer esta vencida para la fecha seleccionada.');
            return;
        }

        if (! $driver->isAvailableBetween($start, $end, $this->assignment->id)) {
            $this->addError('form.driver_id', 'El chofer no esta disponible en el rango seleccionado.');
            return;
        }

        $hasValidTraining = $driver->trainings->first(function (DriverTraining $training) use ($start) {
            return ! $training->expires_at || $training->expires_at->greaterThanOrEqualTo($start);
        });

        if (! $hasValidTraining) {
            $this->addError('form.driver_id', 'El chofer no cuenta con capacitaciones vigentes.');
            return;
        }

        if (in_array($truck->status, ['maintenance', 'out_of_service'], true)) {
            $this->addError('form.truck_id', 'El camion seleccionado no esta disponible (mantenimiento o fuera de servicio).');
            return;
        }

        if ($truck->requiresMaintenanceAlert($start)) {
            $this->addError('form.truck_id', 'El camion requiere mantenimiento antes de la fecha seleccionada.');
            return;
        }

        $pendingMaintenance = $truck->maintenances
            ->filter(fn ($maintenance) => in_array($maintenance->status, ['scheduled', 'in_progress'], true))
            ->first(fn ($maintenance) => $maintenance->maintenance_date && $maintenance->maintenance_date->between($start->copy()->startOfDay(), $end->copy()->endOfDay()));

        if ($pendingMaintenance) {
            $this->addError('form.truck_id', 'El camion tiene un mantenimiento programado en el periodo seleccionado.');
            return;
        }

        DB::transaction(function () use ($data, $start, $end) {
            $originalTruck = $this->assignment->getOriginal('truck_id');
            $originalDriver = $this->assignment->getOriginal('driver_id');
            $originalStatus = $this->assignment->getOriginal('status');

            $this->assignment->fill([
                'order_id' => $data['order_id'],
                'truck_id' => $data['truck_id'],
                'driver_id' => $data['driver_id'],
                'status' => $data['status'],
                'description' => $data['description'],
                'notes' => $data['notes'],
            ]);

            $this->assignment->start_date = $start;
            $this->assignment->end_date = $data['end_date'] ? $end : null;
            $this->assignment->save();

            $this->syncTruckAvailability($originalTruck);
            $this->syncDriverAvailability($originalDriver);
            $this->syncOrderStatus($originalStatus);
        });

        session()->flash('message', $this->isEdit ? 'Asignacion actualizada correctamente.' : 'Asignacion creada correctamente.');
        return redirect()->route('fleet.assignments.index');
    }

    public function render()
    {
        $this->authorize('viewAny', Assignment::class);

        return view('livewire.fleet.assignment-form');
    }

    protected function loadOptions(): void
    {
        $this->authorize('viewAny', Order::class);
        $this->authorize('viewAny', Truck::class);
        $this->authorize('viewAny', Driver::class);

        $orderId = $this->form['order_id'] ?? null;
        $start = isset($this->form['start_date']) ? Carbon::parse($this->form['start_date']) : now();
        $end = isset($this->form['end_date']) && $this->form['end_date'] ? Carbon::parse($this->form['end_date']) : $start->copy();

        $this->orders = Order::query()
            ->where(function ($query) use ($orderId) {
                $query->whereIn('status', ['pending', 'en_route']);
                if ($orderId) {
                    $query->orWhere('id', $orderId);
                }
            })
            ->orderBy('pickup_date')
            ->get();

        $this->trucks = Truck::query()
            ->with('maintenances')
            ->where(function ($query) {
                $query->whereIn('status', ['available', 'in_use']);
                if (! empty($this->form['truck_id'])) {
                    $query->orWhere('id', $this->form['truck_id']);
                }
            })
            ->orderBy('plate_number')
            ->get()
            ->filter(function (Truck $truck) use ($start, $end) {
                if ($this->assignment->truck_id === $truck->id) {
                    return true;
                }

                if ($truck->requiresMaintenanceAlert($start)) {
                    return false;
                }

                $hasPendingMaintenance = $truck->maintenances
                    ->filter(fn ($maintenance) => in_array($maintenance->status, ['scheduled', 'in_progress'], true))
                    ->first(fn ($maintenance) => $maintenance->maintenance_date && $maintenance->maintenance_date->between($start->copy()->startOfDay(), $end->copy()->endOfDay()));

                return ! $hasPendingMaintenance;
            })
            ->values();

        $this->drivers = Driver::query()
            ->with('trainings')
            ->where(function ($query) {
                $query->whereIn('status', ['active', 'assigned']);
                if (! empty($this->form['driver_id'])) {
                    $query->orWhere('id', $this->form['driver_id']);
                }
            })
            ->orderBy('name')
            ->get()
            ->filter(function (Driver $driver) use ($start, $end) {
                if ($this->assignment->driver_id === $driver->id) {
                    return true;
                }

                if (! $driver->hasValidLicenseAt($start)) {
                    return false;
                }

                if (! $driver->isAvailableBetween($start, $end, $this->assignment->id)) {
                    return false;
                }

                return $driver->trainings->contains(fn (DriverTraining $training) => ! $training->expires_at || $training->expires_at->greaterThanOrEqualTo($start));
            })
            ->values();

        // Guardamos una vista previa del pedido seleccionado para alimentar el resumen lateral.
        $this->orderPreview = $this->orders->firstWhere('id', (int) ($this->form['order_id'] ?? 0));
    }

    public function autoAssignResources(): void
    {
        $this->autoAssignAlert = null;

        $start = isset($this->form['start_date']) ? Carbon::parse($this->form['start_date']) : now();
        $end = isset($this->form['end_date']) && $this->form['end_date'] ? Carbon::parse($this->form['end_date']) : $start->copy();

        $truck = $this->findAvailableTruck($start, $end);
        $driver = $this->findAvailableDriver($start, $end);

        if (! $truck || ! $driver) {
            $this->autoAssignAlert = 'No hay camiones o choferes disponibles en el rango seleccionado.';
            return;
        }

        $this->form['truck_id'] = $truck->id;
        $this->form['driver_id'] = $driver->id;
    }

    protected function findAvailableTruck(Carbon $start, Carbon $end): ?Truck
    {
        return Truck::operational()
            ->with('maintenances')
            ->orderBy('next_maintenance')
            ->get()
            ->first(function (Truck $truck) use ($start, $end) {
                if ($this->assignment->truck_id === $truck->id) {
                    return true;
                }

                if ($truck->requiresMaintenanceAlert($start)) {
                    return false;
                }

                $hasOverlap = $this->resourceOccupiedQuery('truck_id', $truck->id, $start, $end)->exists();

                if ($hasOverlap) {
                    return false;
                }

                $hasPendingMaintenance = $truck->maintenances
                    ->filter(fn ($maintenance) => in_array($maintenance->status, ['scheduled', 'in_progress'], true))
                    ->first(fn ($maintenance) => $maintenance->maintenance_date && $maintenance->maintenance_date->between($start->copy()->startOfDay(), $end->copy()->endOfDay()));

                return ! $hasPendingMaintenance;
            });
    }

    protected function findAvailableDriver(Carbon $start, Carbon $end): ?Driver
    {
        return Driver::query()
            ->with('trainings')
            ->whereIn('status', ['active'])
            ->orderBy('license_expiration')
            ->get()
            ->first(function (Driver $driver) use ($start, $end) {
                if (! $driver->hasValidLicenseAt($start)) {
                    return false;
                }

                if (! $driver->isAvailableBetween($start, $end, $this->assignment->id)) {
                    return false;
                }

                return $driver->trainings->contains(fn (DriverTraining $training) => ! $training->expires_at || $training->expires_at->greaterThanOrEqualTo($start));
            });
    }

    protected function resourceOccupied(string $column, int $id, Carbon $start, Carbon $end): bool
    {
        return $this->resourceOccupiedQuery($column, $id, $start, $end)->exists();
    }

    protected function resourceOccupiedQuery(string $column, int $id, Carbon $start, Carbon $end)
    {
        return Assignment::where($column, $id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->when($this->assignment->exists, fn ($query) => $query->where('id', '!=', $this->assignment->id))
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_date', '<=', $start)->where(function ($inner) use ($end) {
                            $inner->whereNull('end_date')->orWhere('end_date', '>=', $end);
                        });
                    })
                    ->orWhereBetween('end_date', [$start, $end]);
            });
    }

    protected function syncTruckAvailability(?int $originalTruck): void
    {
        if ($originalTruck && $originalTruck !== (int) $this->assignment->truck_id) {
            $this->releaseTruck($originalTruck);
        }

        $this->applyTruckStatus((int) $this->assignment->truck_id, $this->assignment->status);
    }

    protected function syncDriverAvailability(?int $originalDriver): void
    {
        if ($originalDriver && $originalDriver !== (int) $this->assignment->driver_id) {
            $this->releaseDriver($originalDriver);
        }

        $this->applyDriverStatus((int) $this->assignment->driver_id, $this->assignment->status);
    }

    protected function syncOrderStatus(?string $originalStatus): void
    {
        $order = $this->assignment->order;

        if (!$order) {
            return;
        }

        if (in_array($this->assignment->status, ['scheduled', 'in_progress'], true)) {
            $order->status = 'en_route';
        }

        if ($this->assignment->status === 'completed') {
            $order->status = 'delivered';
            $order->delivery_date = $this->assignment->end_date ?? now();
        }

        if ($this->assignment->status === 'cancelled') {
            $order->status = 'cancelled';
        }

        if ($originalStatus === 'cancelled' && $this->assignment->status === 'scheduled') {
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
