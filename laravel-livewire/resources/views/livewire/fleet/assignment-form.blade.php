<div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h2 class="text-2xl font-semibold text-token ">{{ $isEdit ? 'Editar Asignacion' : 'Nueva Asignacion' }}</h2>
    <a href="{{ route('fleet.assignments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Volver
    </a>
 </div>

 <div class="surface-card p-6 shadow-lg">
 <form wire:submit.prevent="save" class="grid gap-6">
 <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="form-field">
                <label for="order_id" class="form-label">
                    <span class="required">Orden</span>
                </label>
                <select
                    id="order_id"
                    wire:model="form.order_id"
                    class="form-control form-md @error('form.order_id') is-invalid @enderror"
                    @error('form.order_id') aria-invalid="true" aria-describedby="order_id-error" @enderror
                >
                    <option value="">Seleccione un Orden</option>
                    @foreach($orders as $order)
                        <option value="{{ $order['id'] }}">{{ $order['reference'] }} - {{ $order['origin'] }} -> {{ $order['destination'] }}</option>
                    @endforeach
                </select>
                @error('form.order_id')
                    <p id="order_id-error" class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-field">
                <label class="form-label">
                    <span class="required">Modo de asignación</span>
                </label>
                <div class="flex flex-wrap items-center gap-4">
                    <label class="form-check">
                        <input type="radio" wire:model="mode" value="manual" class="h-4 w-4">
                        <span class="form-check-label">Manual</span>
                    </label>
                    <label class="form-check">
                        <input type="radio" wire:model="mode" value="automatic" class="h-4 w-4">
                        <span class="form-check-label">Automática</span>
                    </label>
                </div>
                @error('mode')
                    <p class="form-error">{{ $message }}</p>
                @enderror
                @if ($mode === 'automatic')
                    <button type="button" wire:click="autoAssignResources" class="btn btn-primary btn-sm mt-3">
                        <i class="fas fa-bolt"></i>
            Buscar recursos disponibles
        </button>
                    @if ($autoAssignAlert)
                        <p class="mt-2 form-help text-warning">{{ $autoAssignAlert }}</p>
                    @endif
                @endif
            </div>

            <div class="form-field">
                <label for="truck_id" class="form-label">
                    <span class="required">Vehículo</span>
                </label>
                <select
                    id="truck_id"
                    wire:model="form.truck_id"
                    class="form-control form-md @error('form.truck_id') is-invalid @enderror"
                    @error('form.truck_id') aria-invalid="true" aria-describedby="truck_id-error" @enderror
                >
                    <option value="">Seleccione un vehiculo</option>
                    @foreach($trucks as $truck)
                        <option value="{{ $truck['id'] }}">{{ $truck['plate_number'] }} - {{ $truck['brand'] }} {{ $truck['model'] }} ({{ $truck['status_label'] }})</option>
                    @endforeach
                </select>
                @error('form.truck_id')
                    <p id="truck_id-error" class="form-error">{{ $message }}</p>
                @enderror
                @if (! empty($form['truck_id']))
                    @php
                        $selectedTruck = collect($trucks)->firstWhere('id', (int) $form['truck_id']);
 @endphp
 @if ($selectedTruck)
                        <div class="mt-3 rounded-lg border border-token bg-surface p-3 text-xs text-token-muted">
                            <p><span class="font-semibold text-token">Mantenimiento prox.:</span> {{ $selectedTruck['next_maintenance'] ?? 'No definido' }}</p>
                            <p><span class="font-semibold text-token">Km acumulado:</span> {{ number_format($selectedTruck['mileage']) }} km</p>
                        </div>
                    @endif
                @endif
            </div>

            <div class="form-field">
                <label for="driver_id" class="form-label">
                    <span class="required">Chofer</span>
                </label>
                <select
                    id="driver_id"
                    wire:model="form.driver_id"
                    class="form-control form-md @error('form.driver_id') is-invalid @enderror"
                    @error('form.driver_id') aria-invalid="true" aria-describedby="driver_id-error" @enderror
                >
                    <option value="">Seleccione un conductor</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver['id'] }}">{{ $driver['full_name'] }} ({{ $driver['status_label'] }})</option>
                    @endforeach
                </select>
                @error('form.driver_id')
                    <p id="driver_id-error" class="form-error">{{ $message }}</p>
                @enderror
                @if (! empty($form['driver_id']))
                    @php
                        $selectedDriver = collect($drivers)->firstWhere('id', (int) $form['driver_id']);
 @endphp
 @if ($selectedDriver)
                        <div class="mt-3 rounded-lg border border-token bg-surface p-3 text-xs text-token-muted">
                            <p><span class="font-semibold text-token">Licencia:</span> {{ $selectedDriver['license_expiration'] ?? '-' }}</p>
                            @if (array_key_exists('valid_trainings_count', $selectedDriver))
                                <p><span class="font-semibold text-token">Capacitaciones vigentes:</span> {{ $selectedDriver['valid_trainings_count'] }}</p>
                            @endif
                        </div>
                    @endif
                @endif
            </div>

            <div class="form-field">
                <label for="start_date" class="form-label">
                    <span class="required">Fecha y hora de inicio</span>
                </label>
                <input
                    type="datetime-local"
                    id="start_date"
                    wire:model="form.start_date"
                    class="form-control form-md @error('form.start_date') is-invalid @enderror"
                    @error('form.start_date') aria-invalid="true" aria-describedby="start_date-error" @enderror
                >
                @error('form.start_date')
                    <p id="start_date-error" class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-field">
                <label for="end_date" class="form-label">Fecha y hora de fin</label>
                <input
                    type="datetime-local"
                    id="end_date"
                    wire:model="form.end_date"
                    class="form-control form-md @error('form.end_date') is-invalid @enderror"
                    @error('form.end_date') aria-invalid="true" aria-describedby="end_date-error" @enderror
                >
                @error('form.end_date')
                    <p id="end_date-error" class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-field">
                <label for="status" class="form-label">
                    <span class="required">Estado</span>
                </label>
                <select
                    id="status"
                    wire:model="form.status"
                    class="form-control form-md @error('form.status') is-invalid @enderror"
                    @error('form.status') aria-invalid="true" aria-describedby="status-error" @enderror
                >
                    <option value="scheduled">Programada</option>
                    <option value="in_progress">En ruta</option>
                    <option value="completed">Completada</option>
                    <option value="cancelled">Cancelada</option>
                </select>
                @error('form.status')
                    <p id="status-error" class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-field md:col-span-2">
                <label for="description" class="form-label">
                    <span class="required">Descripción</span>
                </label>
                <input
                    type="text"
                    id="description"
                    wire:model="form.description"
                    class="form-control form-md @error('form.description') is-invalid @enderror"
                    placeholder="Ej: Transporte Lima - Arequipa"
                    @error('form.description') aria-invalid="true" aria-describedby="description-error" @enderror
                >
                @error('form.description')
                    <p id="description-error" class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="form-field">
            <label for="notes" class="form-label">Notas</label>
            <textarea
                id="notes"
                wire:model="form.notes"
                rows="4"
                class="form-control form-md @error('form.notes') is-invalid @enderror"
                placeholder="Observaciones adicionales"
                @error('form.notes') aria-invalid="true" aria-describedby="notes-error" @enderror
            ></textarea>
            @error('form.notes')
                <p id="notes-error" class="form-error">{{ $message }}</p>
            @enderror
        </div>

 <div class="flex flex-wrap items-center justify-end gap-3">
    <a href="{{ route('fleet.assignments.index') }}" class="btn btn-secondary">
        Cancelar
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i>
        {{ $isEdit ? 'Actualizar' : 'Guardar' }}
    </button>
 </div>
 </form>
 </div>

 @if($orderPreview)
 <div class="surface-card p-6 shadow-lg">
 <h3 class="text-lg font-semibold text-token ">Resumen del Orden</h3>
 <dl class="mt-4 space-y-2 text-sm text-token ">
 <div class="flex items-center gap-2">
 <dt class="font-semibold text-token ">Ruta:</dt>
 <dd>{{ $orderPreview['origin'] }} -> {{ $orderPreview['destination'] }}</dd>
 </div>
 <div class="flex items-center gap-2">
 <dt class="font-semibold text-token ">Estado actual:</dt>
 <dd>{{ $orderPreview['status_label'] }}</dd>
 </div>
 <div>
 <dt class="font-semibold text-token ">Detalle:</dt>
 <dd>{{ $orderPreview['cargo_details'] ?: 'Sin detalle de carga' }}</dd>
 </div>
 </dl>
 </div>
 @endif
</div>
