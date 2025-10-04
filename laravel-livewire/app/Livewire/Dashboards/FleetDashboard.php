<?php

namespace App\Livewire\Dashboards;

use App\Models\Document;
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

        // Paso 3: consultar los mantenimientos programados y enriquecerlos con alias usados en la plantilla.
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

        // Paso 4: detectar licencias de conductores próximas a vencer y exponerlas como "documentos".
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

        // Paso 5: actualizar el indicador de documentos por vencer con la colección calculada.
        $fleetStats['expiringDocuments'] = $expiringDocuments->count();

        return view('livewire.dashboards.fleet-dashboard', [
            'fleetStats' => $fleetStats,
            'upcomingMaintenances' => $upcomingMaintenances,
            'expiringDocuments' => $expiringDocuments,
        ])->layout('components.layouts.dashboard', [
            'title' => __('Panel de flota'),
        ]);
    }
}
