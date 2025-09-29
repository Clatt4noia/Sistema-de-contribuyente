<div class="container mx-auto py-6 space-y-6">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
            {{ $isEdit ? 'Editar Asignacion' : 'Nueva Asignacion' }}
        </h2>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="order_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pedido *</label>
                    <select id="order_id" wire:model="assignment.order_id" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('assignment.order_id') border-red-500 @enderror">
                        <option value="">Seleccione un pedido</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->reference }} - {{ $order->origin }} -> {{ $order->destination }}</option>
                        @endforeach
                    </select>
                    @error('assignment.order_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="truck_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vehiculo *</label>
                    <select id="truck_id" wire:model="assignment.truck_id" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('assignment.truck_id') border-red-500 @enderror">
                        <option value="">Seleccione un vehiculo</option>
                        @foreach($trucks as $truck)
                            <option value="{{ $truck->id }}">{{ $truck->plate_number }} - {{ $truck->brand }} {{ $truck->model }} ({{ ucfirst($truck->status) }})</option>
                        @endforeach
                    </select>
                    @error('assignment.truck_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="driver_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Conductor *</label>
                    <select id="driver_id" wire:model="assignment.driver_id" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('assignment.driver_id') border-red-500 @enderror">
                        <option value="">Seleccione un conductor</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }} {{ $driver->last_name }} ({{ ucfirst($driver->status) }})</option>
                        @endforeach
                    </select>
                    @error('assignment.driver_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha y hora de inicio *</label>
                    <input type="datetime-local" id="start_date" wire:model="assignment.start_date" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('assignment.start_date') border-red-500 @enderror">
                    @error('assignment.start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha y hora de fin</label>
                    <input type="datetime-local" id="end_date" wire:model="assignment.end_date" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('assignment.end_date') border-red-500 @enderror">
                    @error('assignment.end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado *</label>
                    <select id="status" wire:model="assignment.status" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('assignment.status') border-red-500 @enderror">
                        <option value="scheduled">Programada</option>
                        <option value="in_progress">En ruta</option>
                        <option value="completed">Completada</option>
                        <option value="cancelled">Cancelada</option>
                    </select>
                    @error('assignment.status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripcion *</label>
                    <input type="text" id="description" wire:model="assignment.description" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('assignment.description') border-red-500 @enderror" placeholder="Ej: Transporte Lima - Arequipa">
                    @error('assignment.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas</label>
                <textarea id="notes" wire:model="assignment.notes" rows="4" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('assignment.notes') border-red-500 @enderror" placeholder="Observaciones adicionales"></textarea>
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

    @if($assignment->order)
        <div class="mt-6 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">Resumen del pedido</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                <span class="font-semibold">Ruta:</span> {{ $assignment->order->origin }} -> {{ $assignment->order->destination }}
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                <span class="font-semibold">Estado actual:</span> {{ ucfirst($assignment->order->status) }}
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                <span class="font-semibold">Detalle:</span> {{ $assignment->order->cargo_details ?: 'Sin detalle de carga' }}
            </p>
        </div>
    @endif
</div>

