<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Reporte de Flota</h1>
        <a href="{{ route('fleet.assignments.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Ver asignaciones</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-4 bg-white shadow rounded">
            <p class="text-sm text-gray-500">Camiones disponibles</p>
            <p class="text-2xl font-semibold">{{ $truckTotals['available'] ?? 0 }}</p>
        </div>
        <div class="p-4 bg-white shadow rounded">
            <p class="text-sm text-gray-500">Camiones en uso</p>
            <p class="text-2xl font-semibold">{{ $truckTotals['in_use'] ?? 0 }}</p>
        </div>
        <div class="p-4 bg-white shadow rounded">
            <p class="text-sm text-gray-500">Camiones en mantenimiento</p>
            <p class="text-2xl font-semibold">{{ $truckTotals['maintenance'] ?? 0 }}</p>
        </div>
        <div class="p-4 bg-white shadow rounded">
            <p class="text-sm text-gray-500">Pedidos activos</p>
            <p class="text-2xl font-semibold">{{ ($orderTotals['pending'] ?? 0) + ($orderTotals['en_route'] ?? 0) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white shadow rounded p-4">
            <h2 class="text-lg font-semibold mb-3">Conductores</h2>
            <ul class="space-y-2 text-sm text-gray-600">
                <li>Activos: {{ $driverTotals['active'] ?? 0 }}</li>
                <li>Asignados: {{ $driverTotals['assigned'] ?? 0 }}</li>
                <li>Inactivos: {{ $driverTotals['inactive'] ?? 0 }}</li>
                <li>De permiso: {{ $driverTotals['on_leave'] ?? 0 }}</li>
            </ul>
        </div>
        <div class="bg-white shadow rounded p-4">
            <h2 class="text-lg font-semibold mb-3">Asignaciones</h2>
            <ul class="space-y-2 text-sm text-gray-600">
                <li>Programadas: {{ $assignmentsByStatus['scheduled'] ?? 0 }}</li>
                <li>En ruta: {{ $assignmentsByStatus['in_progress'] ?? 0 }}</li>
                <li>Completadas: {{ $assignmentsByStatus['completed'] ?? 0 }}</li>
                <li>Canceladas: {{ $assignmentsByStatus['cancelled'] ?? 0 }}</li>
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white shadow rounded p-4">
            <h2 class="text-lg font-semibold mb-3">Top conductores (mes)</h2>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Conductor</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Asignaciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($topDrivers as $driver)
                        <tr>
                            <td class="px-3 py-2 text-gray-700">{{ $driver->full_name }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ $driver->assignments_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-3 py-2 text-center text-gray-500">Sin asignaciones recientes</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-white shadow rounded p-4">
            <h2 class="text-lg font-semibold mb-3">Mantenimientos proximos</h2>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Vehiculo</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Fecha</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Tipo</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($upcomingMaintenance as $item)
                        <tr>
                            <td class="px-3 py-2 text-gray-700">{{ $item->truck->plate_number }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ $item->maintenance_date->format('d/m/Y') }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ $item->maintenance_type }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-2 text-center text-gray-500">No hay mantenimientos programados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
