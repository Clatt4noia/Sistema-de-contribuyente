<div class="container mx-auto py-6 space-y-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Editar Mantenimiento' : 'Registrar Mantenimiento' }}</h1>
        <a href="{{ route('fleet.maintenance.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            Volver
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Vehículo -->
                <div>
                    <label for="truck_id" class="block text-sm font-medium text-gray-700 mb-1">Vehículo</label>
                    <select id="truck_id" wire:model="form.truck_id" class="w-full px-3 py-2 border rounded-md">
                        <option value="">Seleccione un vehículo</option>
                        @foreach($trucks as $truck)
                            <option value="{{ $truck->id }}">{{ $truck->plate_number }} - {{ $truck->brand }} {{ $truck->model }}</option>
                        @endforeach
                    </select>
                    @error('form.truck_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Fecha de mantenimiento -->
                <div>
                    <label for="maintenance_date" class="block text-sm font-medium text-gray-700 mb-1">Fecha de mantenimiento</label>
                    <input type="date" id="maintenance_date" wire:model="form.maintenance_date"
                        class="w-full px-3 py-2 border rounded-md">
                    @error('form.maintenance_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Tipo de mantenimiento -->
                <div>
                    <label for="maintenance_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de mantenimiento</label>
                    <select id="maintenance_type" wire:model="form.maintenance_type" class="w-full px-3 py-2 border rounded-md">
                        <option value="">Seleccione un tipo</option>
                        <option value="Preventivo">Preventivo</option>
                        <option value="Correctivo">Correctivo</option>
                        <option value="Revisión">Revisión</option>
                        <option value="Cambio de aceite">Cambio de aceite</option>
                        <option value="Cambio de filtros">Cambio de filtros</option>
                        <option value="Cambio de neumáticos">Cambio de neumáticos</option>
                        <option value="Reparación">Reparación</option>
                        <option value="Otro">Otro</option>
                    </select>
                    @error('form.maintenance_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Costo -->
                <div>
                    <label for="cost" class="block text-sm font-medium text-gray-700 mb-1">Costo</label>
                    <input type="number" step="0.01" id="cost" wire:model="form.cost"
                        class="w-full px-3 py-2 border rounded-md">
                    @error('form.cost') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Estado -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select id="status" wire:model="form.status" class="w-full px-3 py-2 border rounded-md">
                        <option value="scheduled">Programado</option>
                        <option value="in_progress">En progreso</option>
                        <option value="completed">Completado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                    @error('form.status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Descripción -->
            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea id="description" wire:model="form.description" rows="3"
                    class="w-full px-3 py-2 border rounded-md"></textarea>
                @error('form.description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Notas -->
            <div class="mt-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notas adicionales</label>
                <textarea id="notes" wire:model="form.notes" rows="3"
                    class="w-full px-3 py-2 border rounded-md"></textarea>
                @error('form.notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    {{ $isEdit ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </form>
    </div>
</div>