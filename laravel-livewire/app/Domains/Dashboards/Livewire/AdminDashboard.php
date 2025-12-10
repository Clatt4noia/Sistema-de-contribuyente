<?php

namespace App\Domains\Dashboards\Livewire;

use App\Models\Assignment;
use App\Models\Client;
use App\Models\Order;
use App\Models\Truck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class AdminDashboard extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('view-dashboard.admin');
    }

    public function render()
    {
        return view('livewire.dashboards.admin-dashboard', [
            'fleetTotals' => [
                'trucks' => Truck::count(),
                'assignments' => Assignment::count(),
            ],
            'ordersCount' => Order::count(),
            'clientsCount' => Client::count(),
        ])->layout('components.layouts.dashboard', [
            'title' => __('Panel administrativo'),
        ]);
    }
}
