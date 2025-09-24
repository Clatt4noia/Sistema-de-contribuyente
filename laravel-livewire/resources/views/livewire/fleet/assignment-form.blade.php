<div>
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
            {{ $isEdit ? 'Editar Asignación' : 'Nueva Asignación' }}
        </h2>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Vehículo -->
                <div>
                    <label for="truck_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vehículo *</label>
                    <select id="truck_id" wire:model="assignment.truck_id" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('assignment.truck_id') border-red-500 @enderror">
                        <option value="">Seleccione un vehículo</option>
                        @foreach($trucks as $truck)
                            <option value="{{ $truck->id }}">{{ $truck->plate_number }} - {{ $truck->brand }} {{ $truck->model }}</option>
                        @endforeach
                    </select>
                    @error('assignment.truck_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Conductor -->
                <div>
                    <label for="driver_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Conductor *</label>
                    <select id="driver_id" wire:model="assignment.driver_id" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('assignment.driver_id') border-red-500 @enderror">
                        <option value="">Seleccione un conductor</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }} {{ $driver->last_name }} - {{ $driver->document_number }}</option>
                        @endforeach
                    </select>
                    @error('assignment.driver_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Fecha de inicio -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha de inicio *</label>
                    <input type="date" id="start_date" wire:model="assignment.start_date" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('assignment.start_date') border-red-500 @enderror">
                    @error('assignment.start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Fecha de fin -->
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha de fin</label>
                    <input type="date" id="end_date" wire:model="assignment.end_date" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('assignment.end_date') border-red-500 @enderror">
                    @error('assignment.end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Estado -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado *</label>
                    <select id="status" wire:model="assignment.status" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('assignment.status') border-red-500 @enderror">
                        <option value="active">Activa</option>
                        <option value="completed">Completada</option>
                        <option value="cancelled">Cancelada</option>
                    </select>
                    @error('assignment.status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Descripción -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción *</label>
                    <input type="text" id="description" wire:model="assignment.description" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('assignment.description') border-red-500 @enderror" placeholder="Ej: Transporte de mercancía a Bogotá">
                    @error('assignment.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Notas adicionales -->
            <div class="mt-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas adicionales</label>
                <textarea id="notes" wire:model="assignment.notes" rows="4" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('assignment.notes') border-red-500 @enderror" placeholder="Información adicional sobre la asignación..."></textarea>
                @error('assignment.notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="mt-6 flex justify-between">
                <a href="{{ route('fleet.assignments.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>{{ $isEdit ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </form>
    </div>
</div>