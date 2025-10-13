<div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <div class="space-y-1">
 <h1 class="text-2xl font-semibold text-slate-900 ">{{ $isEdit ? 'Editar Pedido' : 'Nuevo Pedido' }}</h1>
 <p class="text-sm text-slate-500 ">Gestiona la informacion clave del pedido y planifica su ruta principal.</p>
 </div>
 <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 ">
 Volver
 </a>
 </div>

 @if (session()->has('message'))
 <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm ">
 {{ session('message') }}
 </div>
 @endif

 <div class="surface-card p-6 shadow-lg">
 <form wire:submit.prevent="save" class="grid gap-6">
 <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
 <div class="form-field">
 <label for="client_id" class="form-label">Cliente *</label>
 <select id="client_id" wire:model.defer="form.client_id" class="form-control">
 <option value="">Seleccione un cliente</option>
 @foreach($clients as $client)
 <option value="{{ $client->id }}">{{ $client->business_name }}</option>
 @endforeach
 </select>
 @error('form.client_id') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="reference" class="form-label">Numero de referencia *</label>
 <input id="reference" type="text" wire:model.defer="form.reference" class="form-control">
 @error('form.reference') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="cargo_type_id" class="form-label">Tipo de carga</label>
 <select id="cargo_type_id" wire:model.defer="form.cargo_type_id" class="form-control">
 <option value="">Seleccione una opción</option>
 @foreach($cargoTypes as $type)
 <option value="{{ $type->id }}">{{ $type->name }}</option>
 @endforeach
 </select>
 @error('form.cargo_type_id') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="origin" class="form-label">Origen *</label>
 <input id="origin" type="text" wire:model.defer="form.origin" class="form-control">
 @error('form.origin') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label class="form-label">Coordenadas de origen</label>
 <div class="grid grid-cols-2 gap-3">
 <input type="number" step="0.000001" placeholder="Latitud" wire:model.defer="form.origin_latitude" class="form-control" />
 <input type="number" step="0.000001" placeholder="Longitud" wire:model.defer="form.origin_longitude" class="form-control" />
 </div>
 @error('form.origin_latitude') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 @error('form.origin_longitude') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="destination" class="form-label">Destino *</label>
 <input id="destination" type="text" wire:model.defer="form.destination" class="form-control">
 @error('form.destination') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label class="form-label">Coordenadas de destino</label>
 <div class="grid grid-cols-2 gap-3">
 <input type="number" step="0.000001" placeholder="Latitud" wire:model.defer="form.destination_latitude" class="form-control" />
 <input type="number" step="0.000001" placeholder="Longitud" wire:model.defer="form.destination_longitude" class="form-control" />
 </div>
 @error('form.destination_latitude') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 @error('form.destination_longitude') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="pickup_date" class="form-label">Fecha de recojo</label>
 <input id="pickup_date" type="datetime-local" wire:model.defer="form.pickup_date" class="form-control">
 @error('form.pickup_date') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="delivery_date" class="form-label">Fecha de entrega</label>
 <input id="delivery_date" type="datetime-local" wire:model.defer="form.delivery_date" class="form-control">
 @error('form.delivery_date') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="delivery_window_start" class="form-label">Inicio ventana entrega</label>
 <input id="delivery_window_start" type="datetime-local" wire:model.defer="form.delivery_window_start" class="form-control">
 @error('form.delivery_window_start') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="delivery_window_end" class="form-label">Fin ventana entrega</label>
 <input id="delivery_window_end" type="datetime-local" wire:model.defer="form.delivery_window_end" class="form-control">
 @error('form.delivery_window_end') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="status" class="form-label">Estado *</label>
 <select id="status" wire:model.defer="form.status" class="form-control">
 <option value="pending">Pendiente</option>
 <option value="en_route">En ruta</option>
 <option value="delivered">Entregado</option>
 <option value="cancelled">Cancelado</option>
 </select>
 @error('form.status') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="estimated_distance_km" class="form-label">Distancia estimada (km)</label>
 <input id="estimated_distance_km" type="number" step="0.01" wire:model.defer="form.estimated_distance_km" class="form-control">
 @error('form.estimated_distance_km') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="estimated_duration_hours" class="form-label">Duracion estimada (horas)</label>
 <input id="estimated_duration_hours" type="number" step="0.01" wire:model.defer="form.estimated_duration_hours" class="form-control">
 @error('form.estimated_duration_hours') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="cargo_weight_kg" class="form-label">Peso (kg)</label>
 <input id="cargo_weight_kg" type="number" step="0.01" wire:model.defer="form.cargo_weight_kg" class="form-control">
 @error('form.cargo_weight_kg') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="cargo_volume_m3" class="form-label">Volumen (m³)</label>
 <input id="cargo_volume_m3" type="number" step="0.01" wire:model.defer="form.cargo_volume_m3" class="form-control">
 @error('form.cargo_volume_m3') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 </div>

 <div class="form-field">
 <label for="cargo_details" class="form-label">Detalle de carga</label>
 <textarea id="cargo_details" rows="3" wire:model.defer="form.cargo_details" class="form-control"></textarea>
 @error('form.cargo_details') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="notes" class="form-label">Notas internas</label>
 <textarea id="notes" rows="3" wire:model.defer="form.notes" class="form-control"></textarea>
 @error('form.notes') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="space-y-4 rounded-2xl border border-slate-200 p-4 ">
 <div class="flex flex-wrap items-center justify-between gap-2">
 <h2 class="text-lg font-semibold text-slate-900 ">Plan de ruta principal</h2>
 <span class="text-sm text-slate-500 ">Sincroniza la ruta base del pedido con las asignaciones.</span>
 </div>

 <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
 <div class="form-field">
 <label for="route_planner" class="form-label">Planificador</label>
 <input id="route_planner" type="text" wire:model.defer="routePlan.planner" class="form-control">
 @error('routePlan.planner') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 <div class="form-field">
 <label for="route_map_url" class="form-label">URL del mapa</label>
 <input id="route_map_url" type="url" wire:model.defer="routePlan.map_url" class="form-control" placeholder="https://maps...">
 @error('routePlan.map_url') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 </div>

 <div class="form-field">
 <label for="route_summary" class="form-label">Resumen de la ruta</label>
 <textarea id="route_summary" rows="3" wire:model.defer="routePlan.route_summary" class="form-control" placeholder="Puntos clave de la ruta..."></textarea>
 @error('routePlan.route_summary') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="route_data" class="form-label">Datos adicionales (JSON)</label>
 <textarea id="route_data" rows="3" wire:model.defer="routePlan.route_data" class="form-control" placeholder='{"waypoints": []}'></textarea>
 @error('routePlan.route_data') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 </div>

 <div class="flex items-center justify-end gap-3">
 <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 ">
 Cancelar
 </a>
 <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 ">
 {{ $isEdit ? 'Actualizar' : 'Guardar' }}
 </button>
 </div>
 </form>
 </div>

 @if($isEdit)
 <div class="surface-card p-6 shadow-lg">
 <livewire:orders.route-plan-manager :order="$order" :key="'route-plan-'.$order->id" />
 </div>
 @endif
</div>
