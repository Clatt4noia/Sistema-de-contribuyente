<div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $isEdit ? 'Editar Camion' : 'Registrar Camion' }}</h1>
        <a href="{{ route('fleet.trucks.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-slate-700/70 dark:text-slate-200 dark:hover:bg-slate-900/60">
            Volver
        </a>
    </div>

    <div class="surface-card p-6 shadow-lg">
        <form wire:submit.prevent="save" class="grid gap-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="form-field">
                    <label for="plate_number" class="form-label">Placa</label>
                    <input type="text" id="plate_number" wire:model.defer="form.plate_number" class="form-control">
                    @error('form.plate_number') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="form-field">
                    <label for="brand" class="form-label">Marca</label>
                    <input type="text" id="brand" wire:model.defer="form.brand" class="form-control">
                    @error('form.brand') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="form-field">
                    <label for="model" class="form-label">Modelo</label>
                    <input type="text" id="model" wire:model.defer="form.model" class="form-control">
                    @error('form.model') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="form-field">
                    <label for="year" class="form-label">Ano</label>
                    <input type="number" id="year" wire:model.defer="form.year" class="form-control">
                    @error('form.year') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="form-field">
                    <label for="type" class="form-label">Tipo</label>
                    <select id="type" wire:model.defer="form.type" class="form-control">
                        <option value="">Seleccione un tipo</option>
                        <option value="Camion">Camion</option>
                        <option value="Tractocamion">Tractocamion</option>
                        <option value="Furgon">Furgon</option>
                        <option value="Cisterna">Cisterna</option>
                        <option value="Volquete">Volquete</option>
                    </select>
                    @error('form.type') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="form-field">
                    <label for="capacity" class="form-label">Capacidad (Ton)</label>
                    <input type="number" step="0.01" id="capacity" wire:model.defer="form.capacity" class="form-control">
                    @error('form.capacity') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="form-field">
                    <label for="mileage" class="form-label">Kilometraje</label>
                    <input type="number" id="mileage" wire:model.defer="form.mileage" class="form-control">
                    @error('form.mileage') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="form-field">
                    <label for="status" class="form-label">Estado</label>
                    <select id="status" wire:model.defer="form.status" class="form-control">
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('form.status') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="form-field">
                    <label for="last_maintenance" class="form-label">Ultimo mantenimiento</label>
                    <input type="date" id="last_maintenance" wire:model.defer="form.last_maintenance" class="form-control">
                    @error('form.last_maintenance') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="form-field">
                    <label for="next_maintenance" class="form-label">Proximo mantenimiento</label>
                    <input type="date" id="next_maintenance" wire:model.defer="form.next_maintenance" class="form-control">
                    @error('form.next_maintenance') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-field">
                <label for="technical_details" class="form-label">Detalles tecnicos</label>
                <textarea id="technical_details" wire:model.defer="form.technical_details" rows="4" class="form-control"></textarea>
                @error('form.technical_details') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('fleet.trucks.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200/80 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-slate-700/70 dark:text-slate-200 dark:hover:bg-slate-900/60">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:bg-indigo-400 dark:text-slate-900 dark:hover:bg-indigo-300">
                    {{ $isEdit ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </form>
    </div>

    @if($isEdit)
        @if ($truck->exists)
            <livewire:fleet.document-manager
                :documentable-type="'truck'"
                :documentable-id="$truck->id"
                :key="'truck-documents-' . $truck->id"
            />
        @else
            <div class="surface-card border border-dashed border-slate-300/70 p-6 text-sm text-slate-600 dark:border-slate-700/70 dark:text-slate-300">
                Guarda el camión para poder adjuntar pólizas, SOAT u otros documentos.
            </div>
        @endif
    @else
        <div class="rounded-2xl border border-dashed border-slate-200/80 bg-white/70 p-6 text-sm text-slate-500 dark:border-slate-800/70 dark:bg-slate-900/40 dark:text-slate-400">
            Guarda el camión para habilitar la carga de documentos (SOAT, pólizas, revisiones técnicas).
        </div>
    @endif

    @if($isEdit)
        <div class="surface-card p-6 shadow-lg">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Historial de mantenimiento</h2>
                <a href="{{ route('fleet.maintenance.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-cyan-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-cyan-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-500 dark:bg-cyan-400 dark:text-slate-900 dark:hover:bg-cyan-300">
                    Programar mantenimiento
                </a>
            </div>

            @if(!empty($maintenanceHistory))
                @php
                    $statusTags = [
                        'scheduled' => ['label' => 'Programado', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200'],
                        'in_progress' => ['label' => 'En progreso', 'class' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-200'],
                        'completed' => ['label' => 'Completado', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200'],
                        'cancelled' => ['label' => 'Cancelado', 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-200'],
                    ];
                @endphp
                <div class="mt-6 overflow-x-auto">
                    <table class="surface-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">Fecha</th>
                                <th class="px-4 py-2">Tipo</th>
                                <th class="px-4 py-2">Estado</th>
                                <th class="px-4 py-2">Costo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($maintenanceHistory as $history)
                                @php($status = $statusTags[$history['status']] ?? $statusTags['scheduled'])
                                <tr class="transition hover:bg-slate-900/5 dark:hover:bg-white/10">
                                    <td class="px-4 py-2 text-slate-700 dark:text-slate-200">{{ $history['date'] }}</td>
                                    <td class="px-4 py-2 text-slate-700 dark:text-slate-200">{{ $history['type'] }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $status['class'] }}">
                                            {{ $status['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-slate-700 dark:text-slate-200">
                                        {{ $history['cost'] !== null ? ('$' . number_format((float) $history['cost'], 2)) : 'Sin costo' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mt-6 text-sm text-slate-500 dark:text-slate-300">Sin registros de mantenimiento para este vehiculo.</p>
            @endif
        </div>
    @endif
</div>
