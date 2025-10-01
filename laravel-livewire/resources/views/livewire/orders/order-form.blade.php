<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Editar Pedido' : 'Nuevo Pedido' }}</h1>
        <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Volver</a>
    </div>

    <div class="bg-white shadow rounded p-6">
        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                    <select wire:model.defer="form.client_id" class="w-full px-3 py-2 border rounded @error('form.client_id') border-red-500 @enderror">
                        <option value="">Seleccione un cliente</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->business_name }}</option>
                        @endforeach
                    </select>
                    @error('form.client_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numero de referencia *</label>
                    <input type="text" wire:model.defer="form.reference" class="w-full px-3 py-2 border rounded @error('form.reference') border-red-500 @enderror">
                    @error('form.reference') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Origen *</label>
                    <input type="text" wire:model.defer="form.origin" class="w-full px-3 py-2 border rounded @error('form.origin') border-red-500 @enderror">
                    @error('form.origin') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Destino *</label>
                    <input type="text" wire:model.defer="form.destination" class="w-full px-3 py-2 border rounded @error('form.destination') border-red-500 @enderror">
                    @error('form.destination') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de recojo</label>
                    <input type="datetime-local" wire:model.defer="form.pickup_date" class="w-full px-3 py-2 border rounded @error('form.pickup_date') border-red-500 @enderror">
                    @error('form.pickup_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de entrega</label>
                    <input type="datetime-local" wire:model.defer="form.delivery_date" class="w-full px-3 py-2 border rounded @error('form.delivery_date') border-red-500 @enderror">
                    @error('form.delivery_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado *</label>
                    <select wire:model.defer="form.status" class="w-full px-3 py-2 border rounded @error('form.status') border-red-500 @enderror">
                        <option value="pending">Pendiente</option>
                        <option value="en_route">En ruta</option>
                        <option value="delivered">Entregado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                    @error('form.status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Distancia estimada (km)</label>
                    <input type="number" step="0.01" wire:model.defer="form.estimated_distance_km" class="w-full px-3 py-2 border rounded @error('form.estimated_distance_km') border-red-500 @enderror">
                    @error('form.estimated_distance_km') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duracion estimada (horas)</label>
                    <input type="number" step="0.01" wire:model.defer="form.estimated_duration_hours" class="w-full px-3 py-2 border rounded @error('form.estimated_duration_hours') border-red-500 @enderror">
                    @error('form.estimated_duration_hours') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Detalle de carga</label>
                <textarea rows="3" wire:model.defer="form.cargo_details" class="w-full px-3 py-2 border rounded @error('form.cargo_details') border-red-500 @enderror"></textarea>
                @error('form.cargo_details') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notas internas</label>
                <textarea rows="3" wire:model.defer="form.notes" class="w-full px-3 py-2 border rounded @error('form.notes') border-red-500 @enderror"></textarea>
                @error('form.notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="border-t pt-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-700">Plan de ruta principal</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Planificador</label>
                        <input type="text" wire:model.defer="routePlan.planner" class="w-full px-3 py-2 border rounded @error('routePlan.planner') border-red-500 @enderror">
                        @error('routePlan.planner') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL del mapa</label>
                        <input type="url" wire:model.defer="routePlan.map_url" class="w-full px-3 py-2 border rounded @error('routePlan.map_url') border-red-500 @enderror" placeholder="https://maps...">
                        @error('routePlan.map_url') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Resumen de la ruta</label>
                    <textarea rows="3" wire:model.defer="routePlan.route_summary" class="w-full px-3 py-2 border rounded @error('routePlan.route_summary') border-red-500 @enderror" placeholder="Puntos clave de la ruta..."></textarea>
                    @error('routePlan.route_summary') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Datos adicionales (JSON)</label>
                    <textarea rows="3" wire:model.defer="routePlan.route_data" class="w-full px-3 py-2 border rounded @error('routePlan.route_data') border-red-500 @enderror" placeholder='{"waypoints": []}'></textarea>
                    @error('routePlan.route_data') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">{{ $isEdit ? 'Actualizar' : 'Guardar' }}</button>
            </div>
        </form>
    </div>

    @if($isEdit)
        <div class="bg-white shadow rounded p-6">
            <livewire:orders.route-plan-manager :order="$order" :key="'route-plan-'.$order->id" />
        </div>
    @endif
</div>
