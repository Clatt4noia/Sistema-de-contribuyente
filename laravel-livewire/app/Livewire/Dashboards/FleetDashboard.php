<?php

namespace App\Livewire\Dashboards;

use App\Models\Assignment;
use App\Models\Document;
use App\Models\Driver;
use App\Models\Maintenance;
use App\Models\Truck;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
        // Paso 1: obtener un resumen de estados de la flota (disponibles, en mantenimiento, etc.).
        $statusBreakdown = Truck::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(static fn ($count) => (int) $count);

        // Paso 2: convertir el resumen anterior en los indicadores que espera la vista.
        $fleetStats = [
            // Sumamos estados equivalentes para evitar perder camiones con nomenclaturas distintas.
            'available' => ($statusBreakdown['available'] ?? 0) + ($statusBreakdown['active'] ?? 0),
            // Consideramos diferentes etiquetas comunes para unidades en mantenimiento.
            'inMaintenance' => ($statusBreakdown['in_maintenance'] ?? 0) + ($statusBreakdown['maintenance'] ?? 0),
        ];

        $totalFleet = $statusBreakdown->sum();
        $occupiedCount = max($totalFleet - ($fleetStats['available'] + $fleetStats['inMaintenance']), 0);

        $statusChart = [
            'labels' => [
                __('Disponibles'),
                __('En mantenimiento'),
                __('Ocupados'),
            ],
            'datasets' => [[
                'label' => __('Estado de flota'),
                'data' => [
                    $fleetStats['available'],
                    $fleetStats['inMaintenance'],
                    $occupiedCount,
                ],
                'backgroundColor' => [
                    'rgba(16, 185, 129, 0.85)',
                    'rgba(251, 191, 36, 0.85)',
                    'rgba(59, 130, 246, 0.85)',
                ],
                'borderWidth' => 0,
            ]],
        ];

        // Paso 3: calcular series históricas para mantenimientos programados.
        $monthsBack = 5;
        $currentMonth = now()->startOfMonth();
        $monthsWindow = Collection::times($monthsBack + 1, static function ($index) use ($monthsBack, $currentMonth) {
            return $currentMonth->copy()->subMonths($monthsBack - ($index - 1));
        });

        $maintenanceWindowStart = $monthsWindow->first()->copy();
        $maintenanceWindowEnd = now()->endOfMonth();

        $maintenanceGroups = Maintenance::query()
            ->whereNotNull('maintenance_date')
            ->whereBetween('maintenance_date', [$maintenanceWindowStart, $maintenanceWindowEnd])
            ->get()
            ->groupBy(static function (Maintenance $maintenance) {
                return optional($maintenance->maintenance_date)?->format('Y-m');
            });

        $maintenanceTrend = [
            'labels' => $monthsWindow->map(static fn (Carbon $month) => $month->isoFormat('MMM YY'))->all(),
            'datasets' => [[
                'label' => __('Mantenimientos programados'),
                'data' => $monthsWindow
                    ->map(static function (Carbon $month) use ($maintenanceGroups) {
                        $key = $month->format('Y-m');

                        return isset($maintenanceGroups[$key]) ? $maintenanceGroups[$key]->count() : 0;
                    })
                    ->all(),
                'borderColor' => 'rgba(99, 102, 241, 1)',
                'backgroundColor' => 'rgba(99, 102, 241, 0.18)',
                'tension' => 0.35,
                'fill' => true,
            ]],
        ];

        // Paso 4: agregar métricas de asignaciones por conductor para los últimos 90 días.
        $assignmentWindowStart = now()->copy()->subDays(90);
        $assignments = Assignment::query()
            ->with('driver')
            ->whereNotNull('driver_id')
            ->whereBetween('start_date', [$assignmentWindowStart, now()])
            ->get()
            ->groupBy('driver_id')
            ->map(static function ($rows) {
                /** @var \Illuminate\Support\Collection<int, Assignment> $rows */
                $first = $rows->first();
                $driver = optional($first)->driver;

                return [
                    'driver' => $driver?->full_name ?? __('Chofer sin asignar'),
                    'count' => $rows->count(),
                ];
            })
            ->sortByDesc('count');

        $topAssignments = $assignments->take(5)->values();
        $averageAssignments = $assignments->isNotEmpty() ? round($assignments->avg('count'), 2) : 0;

        $assignmentsChart = [
            'labels' => $topAssignments->pluck('driver')->all(),
            'datasets' => [[
                'label' => __('Asignaciones (últimos 90 días)'),
                'data' => $topAssignments->pluck('count')->all(),
                'backgroundColor' => [
                    'rgba(14, 165, 233, 0.85)',
                    'rgba(34, 211, 238, 0.85)',
                    'rgba(129, 140, 248, 0.85)',
                    'rgba(16, 185, 129, 0.85)',
                    'rgba(251, 113, 133, 0.85)',
                ],
                'borderRadius' => 12,
                'maxBarThickness' => 42,
            ]],
        ];

        // Paso 5: consultar los mantenimientos programados y enriquecerlos con alias usados en la plantilla.
        $upcomingMaintenances = Maintenance::with('truck')
            ->whereDate('maintenance_date', '>=', now()->startOfDay())
            ->orderBy('maintenance_date')
            ->take(5)
            ->get()
            ->map(static function (Maintenance $maintenance) {
                // La vista utiliza "type" y "scheduled_at"; añadimos esas propiedades manteniendo el modelo original.
                $maintenance->type = $maintenance->maintenance_type;
                $maintenance->scheduled_at = $maintenance->maintenance_date;
                $maintenance->responsible = null; // A falta de un campo específico dejamos el valor en null.

                return $maintenance;
            });

        // Paso 6: detectar licencias de conductores próximas a vencer y exponerlas como "documentos".
        $expiringDocuments = Document::query()
            ->with('documentable')
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', now()->addMonths(3))
            ->orderBy('expires_at')
            ->take(5)
            ->get()
            ->map(static function (Document $document) {
                return (object) [
                    'owner_type' => $document->documentable_type,
                    'owner_id' => $document->documentable_id,
                    'resource_label' => $document->owner_label,
                    'name' => $document->title ?: $document->type_label,
                    'expires_at' => $document->expires_at,
                    'status' => $document->status,
                    'status_label' => $document->status_label,
                ];
            });

        if ($expiringDocuments->count() < 5) {
            $driversPending = Driver::query()
                ->whereNotNull('license_expiration')
                ->whereDate('license_expiration', '<=', now()->addMonths(3))
                ->whereNotIn('id', $expiringDocuments
                    ->filter(fn ($item) => $item->owner_type === Driver::class)
                    ->pluck('owner_id')
                    ->all())
                ->orderBy('license_expiration')
                ->take(5 - $expiringDocuments->count())
                ->get()
                ->map(static function (Driver $driver) {
                    $status = optional($driver->license_expiration)->isPast()
                        ? Document::STATUS_EXPIRED
                        : Document::STATUS_WARNING;

                    return (object) [
                        'owner_type' => Driver::class,
                        'owner_id' => $driver->getKey(),
                        'resource_label' => __('Chofer :name', ['name' => $driver->full_name]),
                        'name' => __('Licencia de conducir'),
                        'expires_at' => $driver->license_expiration,
                        'status' => $status,
                        'status_label' => match ($status) {
                            Document::STATUS_EXPIRED => __('Vencido'),
                            default => __('Por vencer'),
                        },
                    ];
                });

            $expiringDocuments = $expiringDocuments->concat($driversPending);
        }

        // Paso 7: actualizar el indicador de documentos por vencer con la colección calculada.
        $fleetStats['expiringDocuments'] = $expiringDocuments->count();

        return view('livewire.dashboards.fleet-dashboard', [
            'fleetStats' => $fleetStats,
            'upcomingMaintenances' => $upcomingMaintenances,
            'expiringDocuments' => $expiringDocuments,
            'statusChart' => $statusChart,
            'maintenanceTrend' => $maintenanceTrend,
            'assignmentsChart' => $assignmentsChart,
            'assignmentsAverage' => $averageAssignments,
        ])->layout('components.layouts.dashboard', [
            'title' => __('Panel de flota'),
        ]);
    }
}
