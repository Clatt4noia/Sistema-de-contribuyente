<?php

namespace App\Domains\Orders\Livewire;

use App\Models\Assignment;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Truck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class OrderList extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    public string $search = '';
    public string $status = '';
    public string $client_id = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'client_id' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingClientId(): void
    {
        $this->resetPage();
    }

    public function deleteOrder(int $orderId): void
    {
        $order = Order::with('assignments')->findOrFail($orderId);

        $this->authorize('delete', $order);

        DB::transaction(function () use ($order) {
            foreach ($order->assignments as $assignment) {
                $this->authorize('delete', $assignment);
                $truckId = $assignment->truck_id;
                $driverId = $assignment->driver_id;

                $assignment->delete();

                if ($truckId) {
                    $this->releaseTruck($truckId);
                }

                if ($driverId) {
                    $this->releaseDriver($driverId);
                }
            }

            $order->delete();
        });

        session()->flash('message', 'Orden eliminado correctamente.');
        $this->resetPage();
    }

    public function updateOrderStatus(int $orderId, string $status): void
    {
        if (!in_array($status, ['pending', 'en_route', 'delivered', 'cancelled'], true)) {
            return;
        }

        $order = Order::findOrFail($orderId);

        $this->authorize('update', $order);

        $order->status = $status;
        if ($status === 'delivered') {
            $order->delivery_date = $order->delivery_date ?? now();
        }

        $order->save();
        session()->flash('message', 'Estado del Orden actualizado.');
    }

    public function render()
    {
        $this->authorize('viewAny', Order::class);

        $orders = Order::query()
            ->with(['client', 'activeAssignment.truck', 'activeAssignment.driver'])
            ->when($this->search, function ($query) {
                $query->where(function ($searchQuery) {
                    $searchQuery->where('reference', 'like', '%' . $this->search . '%')
                        ->orWhere('origin', 'like', '%' . $this->search . '%')
                        ->orWhere('destination', 'like', '%' . $this->search . '%')
                        ->orWhere('cargo_details', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->client_id, function ($query) {
                $query->where('client_id', $this->client_id);
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        $metrics = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'en_route' => Order::where('status', 'en_route')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        $clients = \App\Models\Client::orderBy('business_name')->get();

        return view('livewire.orders.order-list', [
            'orders' => $orders,
            'metrics' => $metrics,
            'clients' => $clients,
        ]);
    }

    protected function releaseTruck(int $truckId): void
    {
        $hasOtherAssignments = Assignment::where('truck_id', $truckId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($hasOtherAssignments) {
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
        $hasOtherAssignments = Assignment::where('driver_id', $driverId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($hasOtherAssignments) {
            return;
        }

        $driver = Driver::find($driverId);
        if ($driver) {
            $driver->status = 'active';
            $driver->save();
        }
    }
}
