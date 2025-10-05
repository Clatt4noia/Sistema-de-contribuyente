<?php

namespace App\Livewire\Dashboards;

use App\Models\Assignment;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\RouteIncident;
use App\Models\RoutePlan;
use App\Models\Truck;
use App\Models\VehicleLocationUpdate;
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
            'en_route' => Order::where('status', 'en_route')->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
        ];

        $deliveriesWithWindow = Order::where('status', 'delivered')
            ->whereNotNull('delivery_window_end')
            ->count();

        $onTimeDeliveries = Order::where('status', 'delivered')
            ->whereNotNull('delivery_window_end')
            ->whereColumn('delivery_date', '<=', 'delivery_window_end')
            ->count();

        $onTimeRate = $deliveriesWithWindow > 0
            ? round(($onTimeDeliveries / $deliveriesWithWindow) * 100, 1)
            : null;

        $averageCost = round(Order::whereNotNull('estimated_cost')->avg('estimated_cost') ?? 0, 2);
        $activeIncidents = RouteIncident::where('status', '!=', 'resolved')->count();
        $openReservations = InventoryReservation::where('status', 'confirmed')->count();

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

        $recentIncidents = RouteIncident::with(['assignment.truck', 'assignment.order'])
            ->latest('reported_at')
            ->take(5)
            ->get();

        $routeHistory = RoutePlan::with('order.client')
            ->latest()
            ->take(5)
            ->get();

        $latestTracking = VehicleLocationUpdate::with(['truck', 'assignment.order'])
            ->latest('reported_at')
            ->take(5)
            ->get();

        return view('livewire.dashboards.logistics-dashboard', [
            'ordersSummary' => $ordersSummary,
            'availableTrucks' => $availableTrucks,
            'upcomingAssignments' => $upcomingAssignments,
            'recentOrders' => $recentOrders,
            'onTimeRate' => $onTimeRate,
            'averageCost' => $averageCost,
            'activeIncidents' => $activeIncidents,
            'openReservations' => $openReservations,
            'recentIncidents' => $recentIncidents,
            'routeHistory' => $routeHistory,
            'latestTracking' => $latestTracking,
        ])->layout('components.layouts.dashboard', [
            'title' => __('Panel logístico'),
        ]);
    }
}
