<?php
namespace App\Domains\Fleet\Livewire;

use App\Exports\FleetReportExport;
use App\Models\Assignment;
use App\Models\Document;
use App\Models\Driver;
use App\Models\Maintenance;
use App\Models\Order;
use App\Models\Truck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
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

        return view('livewire.fleet.report', $this->reportData());
    }

    public function exportPdf()
    {
        if (! class_exists(Pdf::class)) {
            abort(501, __('Instale barryvdh/laravel-dompdf para exportar este reporte.'));
        }

        $pdf = Pdf::loadView('exports.fleet.report', $this->reportData())->setPaper('a4', 'landscape');

        return response()->streamDownload(static fn () => print($pdf->output()), 'reporte-flota.pdf');
    }

    public function exportExcel()
    {
        if (! class_exists(Excel::class)) {
            abort(501, __('Instale maatwebsite/excel para exportar este reporte.'));
        }

        return Excel::download(new FleetReportExport($this->reportData()), 'reporte-flota.xlsx');
    }

    protected function reportData(): array
    {
        $truckTotals = $this->aggregateStatusTotals(
            Truck::query()
                ->selectRaw('LOWER(TRIM(status)) as status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            [
                'active' => 'available',
                'available' => 'available',
                'disponible' => 'available',
                'en_servicio' => 'in_use',
                'ocupado' => 'in_use',
                'in_use' => 'in_use',
                'maintenance' => 'maintenance',
                'in_maintenance' => 'maintenance',
                'mantenimiento' => 'maintenance',
                'taller' => 'maintenance',
            ]
        );

        $driverTotals = $this->aggregateStatusTotals(
            Driver::query()
                ->selectRaw('LOWER(TRIM(status)) as status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            [
                'active' => 'active',
                'activo' => 'active',
                'available' => 'active',
                'assigned' => 'assigned',
                'asignado' => 'assigned',
                'inactive' => 'inactive',
                'inactivo' => 'inactive',
                'baja' => 'inactive',
                'desactivado' => 'inactive',
                'on_leave' => 'on_leave',
                'permiso' => 'on_leave',
                'de permiso' => 'on_leave',
                'leave' => 'on_leave',
            ]
        );

        $assignmentsByStatus = Assignment::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $upcomingMaintenance = Maintenance::with('truck')
            ->whereDate('maintenance_date', '>=', Carbon::today())
            ->orderBy('maintenance_date')
            ->take(5)
            ->get();

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $topDrivers = Driver::withCount(['assignments as assignments_count' => function ($query) use ($monthStart, $monthEnd) {
                $query->where(function ($query) use ($monthStart, $monthEnd) {
                    $query->whereBetween('start_date', [$monthStart, $monthEnd])
                        ->orWhereBetween('end_date', [$monthStart, $monthEnd])
                        ->orWhere(function ($query) use ($monthStart, $monthEnd) {
                            $query->where('start_date', '<=', $monthStart)
                                ->where(function ($query) use ($monthEnd) {
                                    $query->whereNull('end_date')->orWhere('end_date', '>=', $monthEnd);
                                });
                        });
                });
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

        $days = (int) config('documents.expiring_days', 30);
        $today = now()->toDateString();
        $threshold = now()->addDays($days)->toDateString();

        $documentAlerts = Document::with('documentable')
            ->where(function ($query) use ($days) {
                $query->expired()->orWhere(fn ($subQuery) => $subQuery->expiring($days));
            })
            ->orderByRaw(
                'CASE WHEN expires_at IS NULL THEN 2 WHEN expires_at < ? THEN 0 WHEN expires_at <= ? THEN 1 ELSE 2 END',
                [$today, $threshold]
            )
            ->orderBy('expires_at')
            ->take(10)
            ->get();

        return [
            'truckTotals' => $truckTotals,
            'driverTotals' => $driverTotals,
            'assignmentsByStatus' => $assignmentsByStatus,
            'upcomingMaintenance' => $upcomingMaintenance,
            'topDrivers' => $topDrivers,
            'licenseAlerts' => $licenseAlerts,
            'orderTotals' => $orderTotals,
            'documentAlerts' => $documentAlerts,
        ];
    }

    /**
     * Normaliza los estados provenientes de la base de datos para que coincidan con los alias usados en la vista.
     */
    protected function aggregateStatusTotals(Collection $totals, array $aliases = []): Collection
    {
        $normalized = [];

        foreach ($totals as $status => $count) {
            $key = strtolower(trim((string) $status));
            $target = $aliases[$key] ?? $key;

            $normalized[$target] = ($normalized[$target] ?? 0) + (int) $count;
        }

        return collect($normalized);
    }
}
