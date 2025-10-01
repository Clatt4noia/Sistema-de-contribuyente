<?php

namespace App\Livewire\Dashboards;

use App\Models\Driver;
use App\Models\Maintenance;
use App\Models\Truck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class FleetDashboard extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('view-dashboard.fleet');
    }

    public function render()
    {
        $statusBreakdown = Truck::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $scheduledMaintenance = Maintenance::with('truck')
            ->where('status', 'scheduled')
            ->orderBy('maintenance_date')
            ->take(5)
            ->get();

        $expiringLicenses = Driver::query()
            ->whereDate('license_expiration', '<=', now()->addMonths(3))
            ->orderBy('license_expiration')
            ->take(5)
            ->get();

        return view('livewire.dashboards.fleet-dashboard', [
            'statusBreakdown' => $statusBreakdown,
            'scheduledMaintenance' => $scheduledMaintenance,
            'expiringLicenses' => $expiringLicenses,
        ])->layout('components.layouts.dashboard', [
            'title' => __('Panel de flota'),
        ]);
    }
}
