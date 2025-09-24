<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="rounded-xl bg-blue-600 text-white p-4 shadow-md">
                <h3 class="text-lg font-semibold">Gestión de Flota</h3>
                <div class="flex justify-between mt-2">
                    <a href="{{ route('fleet.trucks.index') }}" class="text-sm bg-white text-blue-600 px-2 py-1 rounded">Camiones</a>
                    <a href="{{ route('fleet.drivers.index') }}" class="text-sm bg-white text-blue-600 px-2 py-1 rounded">Conductores</a>
                    <a href="{{ route('fleet.assignments.index') }}" class="text-sm bg-white text-blue-600 px-2 py-1 rounded">Asignaciones</a>
                </div>
        </div>
        
        <div class="grid auto-rows-min gap-4 md:grid-cols-2">
        <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-md">
            <h3 class="text-lg font-semibold mb-3">Camiones Disponibles</h3>
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
                        @foreach(\App\Models\Truck::where('status', 'available')->take(5)->get() as $truck)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $truck->plate_number }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $truck->brand }} {{ $truck->model }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">
                                    Disponible
                                </span>
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
            <h3 class="text-lg font-semibold mb-3">Mantenimientos Programados</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Vehículo</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @foreach(\App\Models\Maintenance::where('status', 'scheduled')->with('truck')->take(5)->get() as $maintenance)
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
