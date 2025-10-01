<?php

namespace App\Livewire\Fleet;

use App\Models\Assignment;
use App\Models\Driver;
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
    public $trucks = [];
    public $drivers = [];
    public $orders = [];

    protected function rules(): array
    {
        return [
            'assignment.order_id' => 'required|exists:orders,id',
            'assignment.truck_id' => 'required|exists:trucks,id',
            'assignment.driver_id' => 'required|exists:drivers,id',
            'assignment.start_date' => 'required|date',
            'assignment.end_date' => 'nullable|date|after_or_equal:assignment.start_date',
            'assignment.status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'assignment.description' => 'required|string|max:255',
            'assignment.notes' => 'nullable|string',
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

        $this->loadOptions();
    }

    public function updatedAssignmentOrderId(): void
    {
        $this->loadOptions();
    }

    public function updatedAssignmentStatus(): void
    {
        $this->loadOptions();
    }

    public function save()
    {
        $this->authorize(
            $this->isEdit ? 'update' : 'create',
            $this->isEdit ? $this->assignment : Assignment::class
        );

        $this->validate();

        $start = Carbon::parse($this->assignment->start_date);
        $end = $this->assignment->end_date ? Carbon::parse($this->assignment->end_date) : $start->copy();

        if ($start->greaterThan($end)) {
            $this->addError('assignment.end_date', 'La fecha de fin debe ser posterior o igual a la fecha de inicio.');
            return;
        }

        if ($this->resourceOccupied('truck_id', (int) $this->assignment->truck_id, $start, $end)) {
            $this->addError('assignment.truck_id', 'El camion seleccionado ya esta asignado en esas fechas.');
            return;
        }

        if ($this->resourceOccupied('driver_id', (int) $this->assignment->driver_id, $start, $end)) {
            $this->addError('assignment.driver_id', 'El chofer seleccionado ya esta asignado en esas fechas.');
            return;
        }

        DB::transaction(function () use ($start, $end) {
            $originalTruck = $this->assignment->getOriginal('truck_id');
            $originalDriver = $this->assignment->getOriginal('driver_id');
            $originalStatus = $this->assignment->getOriginal('status');

            $this->assignment->start_date = $start;
            $this->assignment->end_date = $this->assignment->end_date ? $end : null;
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

        $orderId = $this->assignment->order_id;

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
            ->where(function ($query) {
                $query->where('status', 'available');
                if ($this->assignment->truck_id) {
                    $query->orWhere('id', $this->assignment->truck_id);
                }
            })
            ->orderBy('plate_number')
            ->get();

        $this->drivers = Driver::query()
            ->where(function ($query) {
                $query->whereIn('status', ['active', 'assigned']);
                if ($this->assignment->driver_id) {
                    $query->orWhere('id', $this->assignment->driver_id);
                }
            })
            ->orderBy('name')
            ->get();
    }

    protected function resourceOccupied(string $column, int $resourceId, Carbon $start, Carbon $end): bool
    {
        return Assignment::query()
            ->when($this->assignment->exists, fn ($q) => $q->whereKeyNot($this->assignment->id))
            ->where($column, $resourceId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where(function ($query) use ($start, $end) {
                $query->where('start_date', '<=', $end)
                    ->where(function ($overlap) use ($start) {
                        $overlap->whereNull('end_date')->orWhere('end_date', '>=', $start);
                    });
            })
            ->exists();
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
