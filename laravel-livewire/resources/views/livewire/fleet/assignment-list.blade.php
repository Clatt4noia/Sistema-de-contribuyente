<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Asignaciones de Vehiculos</h2>
        <a href="{{ route('fleet.assignments.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Nueva Asignacion
        </a>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden mb-6">
        <div class="p-4 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por descripcion, pedido, vehiculo o chofer..." class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600">
            </div>
            <div>
                <select wire:model.live="status" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    <option value="">Todos los estados</option>
                    <option value="scheduled">Programada</option>
                    <option value="in_progress">En ruta</option>
                    <option value="completed">Completada</option>
                    <option value="cancelled">Cancelada</option>
                </select>
            </div>
            <div>
                <select wire:model.live="order_id" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    <option value="">Todos los pedidos</option>
                    @foreach($orders as $order)
                        <option value="{{ $order->id }}">{{ $order->reference }} - {{ $order->origin }} -> {{ $order->destination }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="truck_id" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    <option value="">Todos los vehiculos</option>
                    @foreach($trucks as $truck)
                        <option value="{{ $truck->id }}">{{ $truck->plate_number }} - {{ $truck->brand }} {{ $truck->model }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="driver_id" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    <option value="">Todos los conductores</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}">{{ $driver->name }} {{ $driver->last_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pedido</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Vehiculo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Conductor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Inicio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($assignments as $assignment)
                        @php
                            $statusStyles = [
                                'scheduled' => ['label' => 'Programada', 'class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100'],
                                'in_progress' => ['label' => 'En ruta', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100'],
                                'completed' => ['label' => 'Completada', 'class' => 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100'],
                                'cancelled' => ['label' => 'Cancelada', 'class' => 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100'],
                            ];
                            $statusConfig = $statusStyles[$assignment->status] ?? $statusStyles['scheduled'];
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ optional($assignment->order)->reference ?? 'Sin pedido' }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    @if($assignment->order)
                                        {{ $assignment->order->origin }} -> {{ $assignment->order->destination }}
                                    @else
                                        {{ $assignment->description }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $assignment->truck->plate_number }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $assignment->truck->brand }} {{ $assignment->truck->model }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $assignment->driver->name }} {{ $assignment->driver->last_name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $assignment->driver->document_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $assignment->start_date?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $assignment->end_date ? $assignment->end_date->format('d/m/Y H:i') : 'En curso' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusConfig['class'] }}">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('fleet.assignments.edit', $assignment->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <button wire:click="deleteAssignment({{ $assignment->id }})" wire:confirm="?Esta seguro de eliminar esta asignacion?" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No se encontraron asignaciones
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            {{ $assignments->links() }}
        </div>
    </div>
</div>

