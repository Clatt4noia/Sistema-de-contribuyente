<x-layouts.app :title="$isEdit ? __('Editar Camión') : __('Registrar Camión')">
    <div class="container mx-auto py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Editar Camión' : 'Registrar Camión' }}</h1>
            <a href="{{ route('fleet.trucks.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Volver
            </a>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Placa -->
                    <div>
                        <label for="plate_number" class="block text-sm font-medium text-gray-700 mb-1">Placa</label>
                        <input type="text" id="plate_number" wire:model="truck.plate_number" 
                            class="w-full px-3 py-2 border rounded-md">
                        @error('truck.plate_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Marca -->
                    <div>
                        <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                        <input type="text" id="brand" wire:model="truck.brand" 
                            class="w-full px-3 py-2 border rounded-md">
                        @error('truck.brand') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Modelo -->
                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                        <input type="text" id="model" wire:model="truck.model" 
                            class="w-full px-3 py-2 border rounded-md">
                        @error('truck.model') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Año -->
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                        <input type="number" id="year" wire:model="truck.year" 
                            class="w-full px-3 py-2 border rounded-md">
                        @error('truck.year') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tipo -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        <select id="type" wire:model="truck.type" class="w-full px-3 py-2 border rounded-md">
                            <option value="">Seleccione un tipo</option>
                            <option value="Camión">Camión</option>
                            <option value="Tractocamión">Tractocamión</option>
                            <option value="Furgón">Furgón</option>
                            <option value="Cisterna">Cisterna</option>
                            <option value="Volquete">Volquete</option>
                        </select>
                        @error('truck.type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Capacidad -->
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">Capacidad (Ton)</label>
                        <input type="number" step="0.01" id="capacity" wire:model="truck.capacity" 
                            class="w-full px-3 py-2 border rounded-md">
                        @error('truck.capacity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Estado -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select id="status" wire:model="truck.status" class="w-full px-3 py-2 border rounded-md">
                            <option value="available">Disponible</option>
                            <option value="in_use">En uso</option>
                            <option value="maintenance">En mantenimiento</option>
                            <option value="out_of_service">Fuera de servicio</option>
                        </select>
                        @error('truck.status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Último mantenimiento -->
                    <div>
                        <label for="last_maintenance" class="block text-sm font-medium text-gray-700 mb-1">Último mantenimiento</label>
                        <input type="date" id="last_maintenance" wire:model="truck.last_maintenance" 
                            class="w-full px-3 py-2 border rounded-md">
                        @error('truck.last_maintenance') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Próximo mantenimiento -->
                    <div>
                        <label for="next_maintenance" class="block text-sm font-medium text-gray-700 mb-1">Próximo mantenimiento</label>
                        <input type="date" id="next_maintenance" wire:model="truck.next_maintenance" 
                            class="w-full px-3 py-2 border rounded-md">
                        @error('truck.next_maintenance') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Detalles técnicos -->
                <div class="mt-6">
                    <label for="technical_details" class="block text-sm font-medium text-gray-700 mb-1">Detalles técnicos</label>
                    <textarea id="technical_details" wire:model="truck.technical_details" rows="4" 
                        class="w-full px-3 py-2 border rounded-md"></textarea>
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
</x-layouts.app>
