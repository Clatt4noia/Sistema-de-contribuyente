<div class="container mx-auto py-6 space-y-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Editar Camion' : 'Registrar Camion' }}</h1>
        <a href="{{ route('fleet.trucks.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            Volver
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="plate_number" class="block text-sm font-medium text-gray-700 mb-1">Placa</label>
                    <input type="text" id="plate_number" wire:model.defer="truck.plate_number" class="w-full px-3 py-2 border rounded-md">
                    @error('truck.plate_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                    <input type="text" id="brand" wire:model.defer="truck.brand" class="w-full px-3 py-2 border rounded-md">
                    @error('truck.brand') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="model" class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                    <input type="text" id="model" wire:model.defer="truck.model" class="w-full px-3 py-2 border rounded-md">
                    @error('truck.model') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Ano</label>
                    <input type="number" id="year" wire:model.defer="truck.year" class="w-full px-3 py-2 border rounded-md">
                    @error('truck.year') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select id="type" wire:model.defer="truck.type" class="w-full px-3 py-2 border rounded-md">
                        <option value="">Seleccione un tipo</option>
                        <option value="Camion">Camion</option>
                        <option value="Tractocamion">Tractocamion</option>
                        <option value="Furgon">Furgon</option>
                        <option value="Cisterna">Cisterna</option>
                        <option value="Volquete">Volquete</option>
                    </select>
                    @error('truck.type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">Capacidad (Ton)</label>
                    <input type="number" step="0.01" id="capacity" wire:model.defer="truck.capacity" class="w-full px-3 py-2 border rounded-md">
                    @error('truck.capacity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="mileage" class="block text-sm font-medium text-gray-700 mb-1">Kilometraje</label>
                    <input type="number" id="mileage" wire:model.defer="truck.mileage" class="w-full px-3 py-2 border rounded-md">
                    @error('truck.mileage') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select id="status" wire:model.defer="truck.status" class="w-full px-3 py-2 border rounded-md">
                        <option value="available">Disponible</option>
                        <option value="in_use">En uso</option>
                        <option value="maintenance">En mantenimiento</option>
                        <option value="out_of_service">Fuera de servicio</option>
                    </select>
                    @error('truck.status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="last_maintenance" class="block text-sm font-medium text-gray-700 mb-1">Ultimo mantenimiento</label>
                    <input type="date" id="last_maintenance" wire:model.defer="truck.last_maintenance" class="w-full px-3 py-2 border rounded-md">
                    @error('truck.last_maintenance') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="next_maintenance" class="block text-sm font-medium text-gray-700 mb-1">Proximo mantenimiento</label>
                    <input type="date" id="next_maintenance" wire:model.defer="truck.next_maintenance" class="w-full px-3 py-2 border rounded-md">
                    @error('truck.next_maintenance') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="technical_details" class="block text-sm font-medium text-gray-700 mb-1">Detalles tecnicos</label>
                <textarea id="technical_details" wire:model.defer="truck.technical_details" rows="4" class="w-full px-3 py-2 border rounded-md"></textarea>
                @error('truck.technical_details') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    {{ $isEdit ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </form>
    </div>
</div>

    @if($isEdit)
        <div class="mt-6 bg-white shadow-md rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Historial de mantenimiento</h2>
                <a href="{{ route('fleet.maintenance.create') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded">
                    Programar mantenimiento
                </a>
            </div>

            @if(!empty($maintenanceHistory))
                @php
                    $statusTags = [
                        'scheduled' => ['label' => 'Programado', 'class' => 'bg-yellow-100 text-yellow-800'],
                        'in_progress' => ['label' => 'En progreso', 'class' => 'bg-blue-100 text-blue-800'],
                        'completed' => ['label' => 'Completado', 'class' => 'bg-green-100 text-green-800'],
                        'cancelled' => ['label' => 'Cancelado', 'class' => 'bg-red-100 text-red-800'],
                    ];
                @endphp
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">Fecha</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">Tipo</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">Estado</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">Costo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($maintenanceHistory as $history)
                                @php($status = $statusTags[$history['status']] ?? $statusTags['scheduled'])
                                <tr>
                                    <td class="px-4 py-2 text-gray-700">{{ $history['date'] }}</td>
                                    <td class="px-4 py-2 text-gray-700">{{ $history['type'] }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $status['class'] }}">
                                            {{ $status['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-gray-700">
                                        {{ $history['cost'] !== null ? ('$' . number_format((float) $history['cost'], 2)) : 'Sin costo' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500">Sin registros de mantenimiento para este vehiculo.</p>
            @endif
        </div>
    @endif
</div>
