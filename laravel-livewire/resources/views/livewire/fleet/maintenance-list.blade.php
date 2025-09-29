<div class="container mx-auto py-6 space-y-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Mantenimientos de Vehículos</h1>
        <a href="{{ route('fleet.maintenance.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            Registrar Mantenimiento
        </a>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-4 flex flex-col md:flex-row justify-between space-y-4 md:space-y-0 md:space-x-4">
            <div class="flex-1 max-w-md">
                <input type="text" wire:model.live="search" placeholder="Buscar por tipo o descripción..." 
                    class="w-full px-3 py-2 border rounded-md">
            </div>
            <div class="flex-none">
                <select wire:model.live="truck_id" class="px-3 py-2 border rounded-md">
                    <option value="">Todos los vehículos</option>
                    @foreach($trucks as $truck)
                        <option value="{{ $truck->id }}">{{ $truck->plate_number }} - {{ $truck->brand }} {{ $truck->model }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-none">
                <select wire:model.live="status" class="px-3 py-2 border rounded-md">
                    <option value="">Todos los estados</option>
                    <option value="scheduled">Programado</option>
                    <option value="in_progress">En progreso</option>
                    <option value="completed">Completado</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Vehículo
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipo
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Costo
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($maintenances as $maintenance)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $maintenance->truck->plate_number }}</div>
                                <div class="text-xs text-gray-500">{{ $maintenance->truck->brand }} {{ $maintenance->truck->model }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $maintenance->maintenance_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $maintenance->maintenance_type }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">${{ number_format($maintenance->cost, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $maintenance->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($maintenance->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                       ($maintenance->status === 'scheduled' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                    {{ $maintenance->status === 'completed' ? 'Completado' : 
                                       ($maintenance->status === 'in_progress' ? 'En progreso' : 
                                       ($maintenance->status === 'scheduled' ? 'Programado' : 'Cancelado')) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('fleet.maintenance.edit', $maintenance) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                    Editar
                                </a>
                                <button wire:click="deleteMaintenance({{ $maintenance->id }})" wire:confirm="¿Está seguro de eliminar este registro?" 
                                    class="text-red-600 hover:text-red-900">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No se encontraron registros de mantenimiento
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 bg-white border-t border-gray-200">
            {{ $maintenances->links() }}
        </div>
    </div>
</div>