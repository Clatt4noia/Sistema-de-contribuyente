<?php

namespace App\Livewire\Fleet;

use App\Models\Assignment;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Truck;
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
        $assignment = Assignment::with(['truck', 'driver', 'order'])->find($id);
        if (!$assignment) {
            return;
        }

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
                $order->status = $order->assignments()->whereNotIn('status', ['completed', 'cancelled'])->exists()
                    ? 'en_route'
                    : 'pending';
                $order->save();
            }
        });

        session()->flash('message', 'Asignacion eliminada correctamente.');
    }

    public function render()
    {
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
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
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
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($hasActive) {
            return;
        }

        $truck = Truck::find($truckId);
        if ($truck) {
            $truck->status = 'available';
            $truck->save();
        }
    }

    protected function releaseDriver(int $driverId): void
    {
        $hasActive = Assignment::where('driver_id', $driverId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($hasActive) {
            return;
        }

        $driver = Driver::find($driverId);
        if ($driver) {
            $driver->status = 'active';
            $driver->save();
        }
    }
}
