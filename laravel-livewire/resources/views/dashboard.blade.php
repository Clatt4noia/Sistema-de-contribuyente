<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-xl bg-blue-600 text-white p-4 shadow-md">
                <h3 class="text-lg font-semibold">Gestion de Flota</h3>
                <div class="flex flex-wrap gap-2 mt-3">
                    <a href="{{ route('fleet.trucks.index') }}" class="text-sm bg-white text-blue-600 px-2 py-1 rounded">Camiones</a>
                    <a href="{{ route('fleet.drivers.index') }}" class="text-sm bg-white text-blue-600 px-2 py-1 rounded">Conductores</a>
                    <a href="{{ route('fleet.assignments.index') }}" class="text-sm bg-white text-blue-600 px-2 py-1 rounded">Asignaciones</a>
                    <a href="{{ route('fleet.report') }}" class="text-sm bg-white text-blue-600 px-2 py-1 rounded">Reportes</a>
                    <a href="{{ route('fleet.maintenance.index') }}" class="text-sm bg-white text-blue-600 px-2 py-1 rounded">Mantenimientos</a>
                </div>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-md">
                <h3 class="text-lg font-semibold text-gray-700">Operaciones y Finanzas</h3>
                <div class="flex flex-wrap gap-2 mt-3">
                    <a href="{{ route('orders.index') }}" class="text-sm bg-blue-600 text-white px-2 py-1 rounded">Pedidos</a>
                    <a href="{{ route('clients.index') }}" class="text-sm bg-blue-600 text-white px-2 py-1 rounded">Clientes</a>
                    <a href="{{ route('billing.invoices.index') }}" class="text-sm bg-blue-600 text-white px-2 py-1 rounded">Facturas</a>
                    <a href="{{ route('billing.payments.index') }}" class="text-sm bg-blue-600 text-white px-2 py-1 rounded">Pagos</a>
                </div>
            </div>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-2">
            <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-md">
                <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">Camiones disponibles</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Placa</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Modelo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @foreach(\App\Models\Truck::where('status', 'available')->orderBy('plate_number')->take(5)->get() as $truck)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $truck->plate_number }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $truck->brand }} {{ $truck->model }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">Disponible</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-right">
                    <a href="{{ route('fleet.trucks.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Ver todos</a>
                </div>
            </div>

            <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-md">
                <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">Mantenimientos programados</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Vehiculo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @foreach(\App\Models\Maintenance::where('status', 'scheduled')->with('truck')->orderBy('maintenance_date')->take(5)->get() as $maintenance)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $maintenance->truck->plate_number }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $maintenance->maintenance_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $maintenance->maintenance_type }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-right">
                    <a href="{{ route('fleet.maintenance.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Ver todos</a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
