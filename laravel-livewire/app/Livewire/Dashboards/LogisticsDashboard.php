<?php

namespace App\Livewire\Dashboards;

use App\Models\Assignment;
use App\Models\Order;
use App\Models\Truck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class LogisticsDashboard extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('view-dashboard.logistics');
    }

    public function render()
    {
        $orderQuery = Order::query();

        $ordersSummary = [
            'total' => (clone $orderQuery)->count(),
            'inTransit' => Order::where('status', 'in_transit')->count(),
            'scheduled' => Order::whereIn('status', ['scheduled', 'pending'])->count(),
        ];

        $availableTrucks = Truck::whereIn('status', ['available', 'active'])->count();

        $upcomingAssignments = Assignment::with(['order', 'driver', 'truck'])
            ->whereDate('start_date', '>=', now()->startOfDay())
            ->orderBy('start_date')
            ->take(5)
            ->get();

        $recentOrders = Order::with('client')
            ->latest('pickup_date')
            ->take(5)
            ->get();

        return view('livewire.dashboards.logistics-dashboard', [
            'ordersSummary' => $ordersSummary,
            'availableTrucks' => $availableTrucks,
            'upcomingAssignments' => $upcomingAssignments,
            'recentOrders' => $recentOrders,
        ])->layout('components.layouts.dashboard', [
            'title' => __('Panel logístico'),
        ]);
    }
}
