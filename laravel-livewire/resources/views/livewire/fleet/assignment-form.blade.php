<div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $isEdit ? 'Editar Asignacion' : 'Nueva Asignacion' }}</h2>
        <a href="{{ route('fleet.assignments.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-slate-700/70 dark:text-slate-200 dark:hover:bg-slate-900/60">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>

    <div class="surface-card p-6 shadow-lg">
        <form wire:submit.prevent="save" class="grid gap-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="form-field">
                    <label for="order_id" class="form-label">Pedido *</label>
                    <select id="order_id" wire:model="form.order_id" class="form-control @error('form.order_id') border-rose-400 dark:border-rose-400 @enderror">
                        <option value="">Seleccione un pedido</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->reference }} - {{ $order->origin }} -> {{ $order->destination }}</option>
                        @endforeach
                    </select>
                    @error('form.order_id') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="form-field">
                    <label class="form-label">Modo de asignación *</label>
                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="radio" wire:model="mode" value="manual" class="text-indigo-500 focus:ring-indigo-500">
                            Manual
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="radio" wire:model="mode" value="automatic" class="text-indigo-500 focus:ring-indigo-500">
                            Automática
                        </label>
                    </div>
                    @error('mode') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                    @if ($mode === 'automatic')
                        <button type="button" wire:click="autoAssignResources" class="mt-3 inline-flex items-center gap-2 rounded-lg bg-emerald-500 px-3 py-1.5 text-sm font-semibold text-white shadow hover:bg-emerald-600">
                            <i class="fas fa-bolt"></i>
                            Buscar recursos disponibles
                        </button>
                        @if ($autoAssignAlert)
                            <p class="mt-2 text-xs font-medium text-amber-600 dark:text-amber-300">{{ $autoAssignAlert }}</p>
                        @endif
                    @endif
                </div>

                <div class="form-field">
                    <label for="truck_id" class="form-label">Vehiculo *</label>
                    <select id="truck_id" wire:model="form.truck_id" class="form-control @error('form.truck_id') border-rose-400 dark:border-rose-400 @enderror">
                        <option value="">Seleccione un vehiculo</option>
                        @foreach($trucks as $truck)
                            <option value="{{ $truck->id }}">{{ $truck->plate_number }} - {{ $truck->brand }} {{ $truck->model }} ({{ __($truck->status) }})</option>
                        @endforeach
                    </select>
                    @error('form.truck_id') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                    @if ($form['truck_id'])
                        @php
                            $selectedTruck = $trucks->firstWhere('id', (int) $form['truck_id']);
                        @endphp
                        @if ($selectedTruck)
                            <div class="mt-3 rounded-lg border border-slate-200/60 bg-slate-50/70 p-3 text-xs text-slate-600 dark:border-slate-700/60 dark:bg-slate-900/40 dark:text-slate-300">
                                <p><span class="font-semibold">Mantenimiento prox.:</span> {{ optional($selectedTruck->next_maintenance)->format('d/m/Y') ?? 'No definido' }}</p>
                                <p><span class="font-semibold">Km acumulado:</span> {{ number_format($selectedTruck->mileage) }} km</p>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="form-field">
                    <label for="driver_id" class="form-label">Conductor *</label>
                    <select id="driver_id" wire:model="form.driver_id" class="form-control @error('form.driver_id') border-rose-400 dark:border-rose-400 @enderror">
                        <option value="">Seleccione un conductor</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }} {{ $driver->last_name }} ({{ __($driver->status) }})</option>
                        @endforeach
                    </select>
                    @error('form.driver_id') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                    @if ($form['driver_id'])
                        @php
                            $selectedDriver = $drivers->firstWhere('id', (int) $form['driver_id']);
                        @endphp
                        @if ($selectedDriver)
                            <div class="mt-3 rounded-lg border border-slate-200/60 bg-slate-50/70 p-3 text-xs text-slate-600 dark:border-slate-700/60 dark:bg-slate-900/40 dark:text-slate-300">
                                <p><span class="font-semibold">Licencia:</span> {{ optional($selectedDriver->license_expiration)->format('d/m/Y') }}</p>
                                <p><span class="font-semibold">Capacitaciones vigentes:</span> {{ $selectedDriver->trainings->filter(fn($training) => ! $training->expires_at || $training->expires_at->isFuture())->count() }}</p>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="form-field">
                    <label for="start_date" class="form-label">Fecha y hora de inicio *</label>
                    <input type="datetime-local" id="start_date" wire:model="form.start_date" class="form-control @error('form.start_date') border-rose-400 dark:border-rose-400 @enderror">
                    @error('form.start_date') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="form-field">
                    <label for="end_date" class="form-label">Fecha y hora de fin</label>
                    <input type="datetime-local" id="end_date" wire:model="form.end_date" class="form-control @error('form.end_date') border-rose-400 dark:border-rose-400 @enderror">
                    @error('form.end_date') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="form-field">
                    <label for="status" class="form-label">Estado *</label>
                    <select id="status" wire:model="form.status" class="form-control @error('form.status') border-rose-400 dark:border-rose-400 @enderror">
                        <option value="scheduled">Programada</option>
                        <option value="in_progress">En ruta</option>
                        <option value="completed">Completada</option>
                        <option value="cancelled">Cancelada</option>
                    </select>
                    @error('form.status') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="form-field md:col-span-2">
                    <label for="description" class="form-label">Descripcion *</label>
                    <input type="text" id="description" wire:model="form.description" class="form-control @error('form.description') border-rose-400 dark:border-rose-400 @enderror" placeholder="Ej: Transporte Lima - Arequipa">
                    @error('form.description') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-field">
                <label for="notes" class="form-label">Notas</label>
                <textarea id="notes" wire:model="form.notes" rows="4" class="form-control @error('form.notes') border-rose-400 dark:border-rose-400 @enderror" placeholder="Observaciones adicionales"></textarea>
                @error('form.notes') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('fleet.assignments.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-slate-700/70 dark:text-slate-200 dark:hover:bg-slate-900/60">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:bg-indigo-400 dark:text-slate-900 dark:hover:bg-indigo-300">
                    <i class="fas fa-save"></i>
                    {{ $isEdit ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </form>
    </div>

    @if($orderPreview)
        <div class="surface-card p-6 shadow-lg">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Resumen del pedido</h3>
            <dl class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                <div class="flex items-center gap-2">
                    <dt class="font-semibold text-slate-800 dark:text-slate-100">Ruta:</dt>
                    <dd>{{ $orderPreview->origin }} -> {{ $orderPreview->destination }}</dd>
                </div>
                <div class="flex items-center gap-2">
                    <dt class="font-semibold text-slate-800 dark:text-slate-100">Estado actual:</dt>
                    <dd>{{ __($orderPreview->status) }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-800 dark:text-slate-100">Detalle:</dt>
                    <dd>{{ $orderPreview->cargo_details ?: 'Sin detalle de carga' }}</dd>
                </div>
            </dl>
        </div>
    @endif
</div>
