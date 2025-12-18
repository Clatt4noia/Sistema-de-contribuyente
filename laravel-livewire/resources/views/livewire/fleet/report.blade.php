<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    {{-- HEADER --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-token">Reporte de Flota</h1>
            <p class="mt-1 text-sm text-token">
                Visión general de disponibilidad, uso y riesgos de documentación.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" wire:click="exportPdf" class="btn btn-secondary">
                <i class="fas fa-file-pdf text-danger"></i>
                PDF
            </button>
            <button type="button" wire:click="exportExcel" class="btn btn-secondary">
                <i class="fas fa-file-excel text-success"></i>
                Excel
            </button>
            <a href="{{ route('fleet.assignments.index') }}" class="btn btn-primary">
                Ver asignaciones
            </a>
        </div>
    </div>

    {{-- KPIs PRINCIPALES --}}
    <section class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="surface-card flex items-center justify-between gap-3 rounded-2xl p-4 shadow-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-token">Camiones disponibles</p>
                <p class="mt-2 text-2xl font-semibold text-token">
                    {{ $truckTotals['available'] ?? 0 }}
                </p>
            </div>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-success-soft text-success-strong">
                <i class="fas fa-truck"></i>
            </span>
        </div>

        <div class="surface-card flex items-center justify-between gap-3 rounded-2xl p-4 shadow-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-token">Camiones en uso</p>
                <p class="mt-2 text-2xl font-semibold text-token">
                    {{ $truckTotals['in_use'] ?? 0 }}
                </p>
            </div>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-accent-soft text-accent">
                <i class="fas fa-route"></i>
            </span>
        </div>

        <div class="surface-card flex items-center justify-between gap-3 rounded-2xl p-4 shadow-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-token">
                    Camiones en mantenimiento
                </p>
                <p class="mt-2 text-2xl font-semibold text-token">
                    {{ $truckTotals['maintenance'] ?? 0 }}
                </p>
            </div>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-warning-soft text-warning">
                <i class="fas fa-screwdriver-wrench"></i>
            </span>
        </div>

        <div class="surface-card flex items-center justify-between gap-3 rounded-2xl p-4 shadow-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-token">Orden activos</p>
                <p class="mt-2 text-2xl font-semibold text-token">
                    {{ ($orderTotals['pending'] ?? 0) + ($orderTotals['en_route'] ?? 0) }}
                </p>
            </div>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-surface-strong text-token">
                <i class="fas fa-box"></i>
            </span>
        </div>
    </section>

    {{-- RESUMEN CONDUCTORES / ASIGNACIONES --}}
    <section class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="surface-card rounded-2xl p-4 shadow-sm">
            <h2 class="text-lg font-semibold text-token">Conductores</h2>
            <ul class="mt-3 space-y-2 text-sm text-token">
                <li>Activos: <span class="font-semibold">{{ $driverTotals['active'] ?? 0 }}</span></li>
                <li>Asignados: <span class="font-semibold">{{ $driverTotals['assigned'] ?? 0 }}</span></li>
                <li>Inactivos: <span class="font-semibold">{{ $driverTotals['inactive'] ?? 0 }}</span></li>
                <li>De permiso: <span class="font-semibold">{{ $driverTotals['on_leave'] ?? 0 }}</span></li>
            </ul>
        </div>

        <div class="surface-card rounded-2xl p-4 shadow-sm">
            <h2 class="text-lg font-semibold text-token">Asignaciones</h2>
            <ul class="mt-3 space-y-2 text-sm text-token">
                <li>Programadas: <span class="font-semibold">{{ $assignmentsByStatus['scheduled'] ?? 0 }}</span></li>
                <li>En ruta: <span class="font-semibold">{{ $assignmentsByStatus['in_progress'] ?? 0 }}</span></li>
                <li>Completadas: <span class="font-semibold">{{ $assignmentsByStatus['completed'] ?? 0 }}</span></li>
                <li>Canceladas: <span class="font-semibold">{{ $assignmentsByStatus['cancelled'] ?? 0 }}</span></li>
            </ul>
        </div>
    </section>

    {{-- BLOQUE ANALÍTICO: TABLAS --}}
    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        {{-- Columna izquierda: tres tablas pequeñas apiladas --}}
        <div class="space-y-4 xl:col-span-2">
            <div class="surface-card rounded-2xl p-4 shadow-sm">
                <h2 class="text-lg font-semibold text-token">Top conductores (mes)</h2>
                <table class="table table-sm mt-3">
                    <thead>
                        <tr class="table-row">
                            <th class="table-header">Conductor</th>
                            <th class="table-header">Asignaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topDrivers as $driver)
                            <tr class="table-row table-row-hover">
                                <td class="table-cell text-token">{{ $driver->full_name }}</td>
                                <td class="table-cell text-token">{{ $driver->assignments_count }}</td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="2" class="table-empty">Sin asignaciones recientes</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="surface-card rounded-2xl p-4 shadow-sm">
                <h2 class="text-lg font-semibold text-token">Licencias por vencer (30 días)</h2>
                <table class="table table-sm mt-3">
                    <thead>
                        <tr class="table-row">
                            <th class="table-header">Conductor</th>
                            <th class="table-header">Vence</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($licenseAlerts as $driver)
                            <tr class="table-row table-row-hover">
                                <td class="table-cell text-token">{{ $driver->full_name }}</td>
                                <td
                                    class="table-cell {{ $driver->license_expiration->isPast() ? 'text-danger-strong font-semibold' : 'text-warning font-semibold' }}"
                                >
                                    {{ $driver->license_expiration->format('d/m/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="2" class="table-empty">Sin alertas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="surface-card rounded-2xl p-4 shadow-sm">
                <h2 class="text-lg font-semibold text-token">Mantenimientos próximos</h2>
                <table class="table table-sm mt-3">
                    <thead>
                        <tr class="table-row">
                            <th class="table-header">Vehículo</th>
                            <th class="table-header">Fecha</th>
                            <th class="table-header">Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingMaintenance as $item)
                            <tr class="table-row table-row-hover">
                                <td class="table-cell text-token">{{ $item->truck->plate_number }}</td>
                                <td class="table-cell text-token">{{ $item->maintenance_date->format('d/m/Y') }}</td>
                                <td class="table-cell text-token">{{ $item->maintenance_type }}</td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="3" class="table-empty">No hay mantenimientos programados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Columna derecha: documentos críticos, ocupa alto completo en xl --}}
        <div class="surface-card rounded-2xl p-4 shadow-sm xl:row-span-3">
            <h2 class="text-lg font-semibold text-token">Documentos críticos</h2>

            <table class="table table-sm mt-3 w-full">
                <thead>
                    <tr class="table-row">
                        <th class="table-header">Recurso</th>
                        <th class="table-header">Documento</th>
                        <th class="table-header">Vence</th>
                        <th class="table-header">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $statusClasses = [
                            \App\Models\Document::STATUS_WARNING => 'bg-warning-soft text-warning',
                            \App\Models\Document::STATUS_EXPIRED => 'bg-danger-soft text-danger-strong',
                            \App\Models\Document::STATUS_VALID => 'bg-success-soft text-success-strong',
                        ];
                    @endphp

                    @forelse($documentAlerts as $document)
                        <tr class="table-row table-row-hover">
                            <td class="table-cell text-token">{{ $document->owner_label }}</td>
                            <td class="table-cell text-token">{{ $document->title ?: $document->type_label }}</td>
                            <td class="table-cell text-token">
                                {{ optional($document->expires_at)?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="table-cell">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$document->status] ?? $statusClasses[\App\Models\Document::STATUS_WARNING] }}"
                                >
                                    {{ $document->status_label }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="4" class="table-empty">
                                Sin documentos con alertas de vigencia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
