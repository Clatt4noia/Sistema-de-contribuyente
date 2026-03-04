<?php

namespace App\Domains\Fleet\Livewire;

use App\Enums\Fleet\AssignmentStatus;
use App\Enums\Fleet\DriverStatus;
use App\Enums\Fleet\TruckStatus;
use App\Enums\Orders\OrderStatus;
use App\Models\Assignment;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Truck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app.sidebar', ['title' => 'Asignaciones'])]
#[Title('Asignaciones')]
class AssignmentList extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    public string $search = '';
    public string $status = '';
    public string $truck_id = '';
    public string $driver_id = '';
    public string $order_id = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'truck_id' => ['except' => ''],
        'driver_id' => ['except' => ''],
        'order_id' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->order_id = request()->get('order', $this->order_id);
        $this->truck_id = request()->get('truck', $this->truck_id);
        $this->driver_id = request()->get('driver', $this->driver_id);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingTruckId(): void
    {
        $this->resetPage();
    }

    public function updatingDriverId(): void
    {
        $this->resetPage();
    }

    public function updatingOrderId(): void
    {
        $this->resetPage();
    }

    public function deleteAssignment(int $id): void
    {
        $assignment = Assignment::with(['truck', 'driver', 'order'])->findOrFail($id);

        $this->authorize('delete', $assignment);

        DB::transaction(function () use ($assignment) {
            $truckId = $assignment->truck_id;
            $driverId = $assignment->driver_id;
            $order = $assignment->order;

            $assignment->delete();

            if ($truckId) {
                $this->releaseTruck($truckId);
            }

            if ($driverId) {
                $this->releaseDriver($driverId);
            }

            if ($order) {
                $hasActiveAssignments = $order->assignments()
                    ->whereNotIn('status', [AssignmentStatus::Completed->value, AssignmentStatus::Cancelled->value])
                    ->exists();

                $order->status = $hasActiveAssignments ? OrderStatus::EnRoute : OrderStatus::Pending;
                $order->save();
            }
        });

        session()->flash('message', 'Asignación eliminada correctamente.');
    }

    public function render()
    {
        $this->authorize('viewAny', Assignment::class);

        $assignments = Assignment::query()
            ->with(['truck', 'driver', 'order'])
            ->when($this->search, function ($query) {
                $query->where(function ($searchQuery) {
                    $searchQuery->where('description', 'like', '%' . $this->search . '%')
                        ->orWhereHas('truck', function ($q) {
                            $q->where('plate_number', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('driver', function ($q) {
                            $q->where('name', 'like', '%' . $this->search . '%')
                              ->orWhere('last_name', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('order', function ($q) {
                            $q->where('reference', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status, function ($query) {
                $status = AssignmentStatus::tryFrom($this->status);

                if ($status) {
                    $query->where('status', $status->value);
                }
            })
            ->when($this->truck_id, fn ($query) => $query->where('truck_id', $this->truck_id))
            ->when($this->driver_id, fn ($query) => $query->where('driver_id', $this->driver_id))
            ->when($this->order_id, fn ($query) => $query->where('order_id', $this->order_id))
            ->orderByDesc('start_date')
            ->paginate(10);

        return view('livewire.fleet.assignment-list', [
            'assignments' => $assignments,
            'trucks' => Truck::orderBy('plate_number')->get(),
            'drivers' => Driver::orderBy('name')->get(),
            'orders' => Order::orderBy('reference')->get(),
        ]);
    }

    protected function releaseTruck(int $truckId): void
    {
        $hasActive = Assignment::where('truck_id', $truckId)
            ->whereNotIn('status', [AssignmentStatus::Completed->value, AssignmentStatus::Cancelled->value])
            ->exists();

        if ($hasActive) {
            return;
        }

        $truck = Truck::find($truckId);
        if ($truck) {
            $truck->status = TruckStatus::Available;
            $truck->save();
        }
    }

    protected function releaseDriver(int $driverId): void
    {
        $hasActive = Assignment::where('driver_id', $driverId)
            ->whereNotIn('status', [AssignmentStatus::Completed->value, AssignmentStatus::Cancelled->value])
            ->exists();

        if ($hasActive) {
            return;
        }

        $driver = Driver::find($driverId);
        if ($driver) {
            $driver->status = DriverStatus::Active;
            $driver->save();
        }
    }
}
