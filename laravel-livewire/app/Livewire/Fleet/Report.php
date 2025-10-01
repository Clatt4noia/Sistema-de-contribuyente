<?php
namespace App\Livewire\Fleet;

use App\Models\Assignment;
use App\Models\Driver;
use App\Models\Maintenance;
use App\Models\Order;
use App\Models\Truck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app.sidebar', ['title' => 'Reporte de Flota'])]
#[Title('Reporte de Flota')]
class Report extends Component
{
    use AuthorizesRequests;

    public function render()
    {
        $this->authorize('viewAny', Truck::class);

        $truckTotals = Truck::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $driverTotals = Driver::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $assignmentsByStatus = Assignment::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $upcomingMaintenance = Maintenance::with('truck')
            ->whereDate('maintenance_date', '>=', Carbon::today())
            ->orderBy('maintenance_date')
            ->take(5)
            ->get();

        $topDrivers = Driver::withCount(['assignments' => function ($query) {
                $query->whereBetween('start_date', [now()->startOfMonth(), now()->endOfMonth()]);
            }])
            ->orderByDesc('assignments_count')
            ->take(5)
            ->get();

        $licenseAlerts = Driver::query()
            ->whereDate('license_expiration', '<=', Carbon::now()->addDays(30))
            ->orderBy('license_expiration')
            ->take(5)
            ->get();

        $orderTotals = Order::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('livewire.fleet.report', [
            'truckTotals' => $truckTotals,
            'driverTotals' => $driverTotals,
            'assignmentsByStatus' => $assignmentsByStatus,
            'upcomingMaintenance' => $upcomingMaintenance,
            'topDrivers' => $topDrivers,
            'licenseAlerts' => $licenseAlerts,
            'orderTotals' => $orderTotals,
        ]);
    }
}
