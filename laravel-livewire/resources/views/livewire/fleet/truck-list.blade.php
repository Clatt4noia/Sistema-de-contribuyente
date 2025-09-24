<div class="container mx-auto py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Gestión de Camiones</h1>
        <a href="{{ route('fleet.trucks.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            Agregar Camión
        </a>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-4 flex gap-4">
        <div class="flex-1">
            <input wire:model.live="search" type="text" placeholder="Buscar por placa, marca o modelo..." 
                class="w-full px-4 py-2 border rounded">
        </div>
        <div>
            <select wire:model.live="status" class="px-4 py-2 border rounded">
                <option value="">Todos los estados</option>
                <option value="available">Disponible</option>
                <option value="in_use">En uso</option>
                <option value="maintenance">En mantenimiento</option>
                <option value="out_of_service">Fuera de servicio</option>
            </select>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Placa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marca/Modelo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Año</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Próx. Mant.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($trucks as $truck)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $truck->plate_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $truck->brand }} {{ $truck->model }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $truck->year }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $truck->type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $truck->status === 'available' ? 'bg-green-100 text-green-800' : 
                                   ($truck->status === 'in_use' ? 'bg-blue-100 text-blue-800' : 
                                   ($truck->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                {{ $truck->status === 'available' ? 'Disponible' : 
                                   ($truck->status === 'in_use' ? 'En uso' : 
                                   ($truck->status === 'maintenance' ? 'En mantenimiento' : 'Fuera de servicio')) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $truck->next_maintenance ? $truck->next_maintenance->format('d/m/Y') : 'No programado' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('fleet.trucks.edit', $truck) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</a>
                            <button wire:click="deleteTruck({{ $truck->id }})" wire:confirm="¿Está seguro de eliminar este camión?" 
                                class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center">No hay camiones registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $trucks->links() }}
    </div>
</div>