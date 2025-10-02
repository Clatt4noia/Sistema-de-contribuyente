<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Reporte de Flota</h1>
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" wire:click="exportPdf" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700/70 dark:text-slate-200 dark:hover:bg-slate-900/60">
                <i class="fas fa-file-pdf text-rose-500"></i>
                PDF
            </button>
            <button type="button" wire:click="exportExcel" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700/70 dark:text-slate-200 dark:hover:bg-slate-900/60">
                <i class="fas fa-file-excel text-emerald-500"></i>
                Excel
            </button>
            <a href="{{ route('fleet.assignments.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:bg-indigo-400 dark:text-slate-900 dark:hover:bg-indigo-300">Ver asignaciones</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-slate-500 dark:text-slate-300">Camiones disponibles</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $truckTotals['available'] ?? 0 }}</p>
        </div>
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-slate-500 dark:text-slate-300">Camiones en uso</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $truckTotals['in_use'] ?? 0 }}</p>
        </div>
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-slate-500 dark:text-slate-300">Camiones en mantenimiento</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $truckTotals['maintenance'] ?? 0 }}</p>
        </div>
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-slate-500 dark:text-slate-300">Pedidos activos</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ ($orderTotals['pending'] ?? 0) + ($orderTotals['en_route'] ?? 0) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="surface-card p-4 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Conductores</h2>
            <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                <li>Activos: {{ $driverTotals['active'] ?? 0 }}</li>
                <li>Asignados: {{ $driverTotals['assigned'] ?? 0 }}</li>
                <li>Inactivos: {{ $driverTotals['inactive'] ?? 0 }}</li>
                <li>De permiso: {{ $driverTotals['on_leave'] ?? 0 }}</li>
            </ul>
        </div>
        <div class="surface-card p-4 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Asignaciones</h2>
            <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                <li>Programadas: {{ $assignmentsByStatus['scheduled'] ?? 0 }}</li>
                <li>En ruta: {{ $assignmentsByStatus['in_progress'] ?? 0 }}</li>
                <li>Completadas: {{ $assignmentsByStatus['completed'] ?? 0 }}</li>
                <li>Canceladas: {{ $assignmentsByStatus['cancelled'] ?? 0 }}</li>
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="surface-card p-4 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Top conductores (mes)</h2>
            <table class="surface-table mt-3">
                <thead>
                    <tr>
                        <th class="px-3 py-2">Conductor</th>
                        <th class="px-3 py-2">Asignaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topDrivers as $driver)
                        <tr class="transition hover:bg-slate-900/5 dark:hover:bg-white/10">
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $driver->full_name }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $driver->assignments_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-3 py-2 text-center text-slate-500 dark:text-slate-400">Sin asignaciones recientes</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="surface-card p-4 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Licencias por vencer (30 dias)</h2>
            <table class="surface-table mt-3">
                <thead>
                    <tr>
                        <th class="px-3 py-2">Conductor</th>
                        <th class="px-3 py-2">Vence</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($licenseAlerts as $driver)
                        <tr class="transition hover:bg-slate-900/5 dark:hover:bg-white/10">
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $driver->full_name }}</td>
                            <td class="px-3 py-2 {{ $driver->license_expiration->isPast() ? 'text-rose-500 font-semibold dark:text-rose-300' : 'text-amber-500 font-semibold dark:text-amber-300' }}">
                                {{ $driver->license_expiration->format('d/m/Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-3 py-2 text-center text-slate-500 dark:text-slate-400">Sin alertas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="surface-card p-4 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Mantenimientos proximos</h2>
            <table class="surface-table mt-3">
                <thead>
                    <tr>
                        <th class="px-3 py-2">Vehiculo</th>
                        <th class="px-3 py-2">Fecha</th>
                        <th class="px-3 py-2">Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($upcomingMaintenance as $item)
                        <tr class="transition hover:bg-slate-900/5 dark:hover:bg-white/10">
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $item->truck->plate_number }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $item->maintenance_date->format('d/m/Y') }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $item->maintenance_type }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-2 text-center text-slate-500 dark:text-slate-400">No hay mantenimientos programados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
