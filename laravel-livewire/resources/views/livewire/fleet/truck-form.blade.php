<div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold text-token">{{ $isEdit ? 'Editar Camión' : 'Registrar Camión' }}</h1>
        <a href="{{ route('fleet.trucks.index') }}" class="btn btn-secondary">Volver</a>
    </div>

    <div class="surface-card p-6 shadow-lg">
        <form wire:submit.prevent="save" class="space-y-6">

            {{-- SECCIÓN 1: Datos básicos del vehículo --}}
            <div>
                <h3 class="text-sm font-semibold text-token uppercase tracking-wide mb-4">Datos del vehículo</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    <div class="form-field">
                        <label for="plate_number" class="form-label">Placa <span class="text-danger-strong">*</span></label>
                        <input type="text" id="plate_number" wire:model.defer="form.plate_number"
                            class="form-control uppercase" placeholder="Ej. ABC-123" maxlength="20">
                        @error('form.plate_number') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="type" class="form-label">Tipo de vehículo <span class="text-danger-strong">*</span></label>
                        <select id="type" wire:model.defer="form.type" class="form-control">
                            <option value="">Seleccione un tipo</option>
                            <option value="Tractocamion">Tractocamión (tracto)</option>
                            <option value="Camion">Camión</option>
                            <option value="Semirremolque">Semirremolque / Carreta</option>
                            <option value="Furgon">Furgón</option>
                            <option value="Cisterna">Cisterna</option>
                            <option value="Volquete">Volquete</option>
                            <option value="Plataforma">Plataforma</option>
                        </select>
                        @error('form.type') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="brand" class="form-label">Marca <span class="text-danger-strong">*</span></label>
                        <input type="text" id="brand" wire:model.defer="form.brand"
                            class="form-control" placeholder="Ej. Volvo, Scania, Freightliner">
                        @error('form.brand') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="model" class="form-label">Modelo <span class="text-danger-strong">*</span></label>
                        <input type="text" id="model" wire:model.defer="form.model"
                            class="form-control" placeholder="Ej. FH 460, R 450">
                        @error('form.model') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="year" class="form-label">Año de fabricación <span class="text-danger-strong">*</span></label>
                        <input type="number" id="year" wire:model.defer="form.year"
                            class="form-control" min="1950" max="{{ date('Y') + 1 }}" placeholder="{{ date('Y') }}">
                        @error('form.year') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="capacity" class="form-label">Capacidad de carga (toneladas)</label>
                        <input type="number" step="0.01" id="capacity" wire:model.defer="form.capacity"
                            class="form-control" min="0" placeholder="Ej. 30.5">
                        @error('form.capacity') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="mileage" class="form-label">Kilometraje actual (km)</label>
                        <input type="number" id="mileage" wire:model.defer="form.mileage"
                            class="form-control" min="0" placeholder="Ej. 150000">
                        @error('form.mileage') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="status" class="form-label">Estado <span class="text-danger-strong">*</span></label>
                        <select id="status" wire:model.defer="form.status" class="form-control">
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('form.status') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                </div>
            </div>

            {{-- SECCIÓN 2: Habilitación MTC --}}
            <div class="border-t pt-4">
                <h3 class="text-sm font-semibold text-token uppercase tracking-wide mb-4">Habilitación MTC</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    <div class="form-field">
                        <label for="mtc_registration_number" class="form-label">N° Habilitación MTC (empresa)</label>
                        <input type="text" id="mtc_registration_number"
                            wire:model.defer="form.mtc_registration_number"
                            class="form-control" placeholder="Ej. 1586716CNG" autocomplete="off">
                        <p class="text-xs text-gray-400 mt-1">Número de habilitación de la empresa transportista ante el MTC.</p>
                        @error('form.mtc_registration_number') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="tuce_number" class="form-label">N° TUCE (por vehículo)</label>
                        <input type="text" id="tuce_number"
                            wire:model.defer="form.tuce_number"
                            class="form-control" placeholder="Ej. 151705003" autocomplete="off">
                        <p class="text-xs text-gray-400 mt-1">Tarjeta Única de Circulación Electrónica — asignada por el MTC a este vehículo específico.</p>
                        @error('form.tuce_number') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="special_auth_issuer" class="form-label">Entidad emisora de autorización especial</label>
                        <input type="text" id="special_auth_issuer"
                            wire:model.defer="form.special_auth_issuer"
                            class="form-control" placeholder="Ej. MTC, SUCAMEC, OSINERGMIN">
                        @error('form.special_auth_issuer') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="special_auth_number" class="form-label">N° de autorización especial</label>
                        <input type="text" id="special_auth_number"
                            wire:model.defer="form.special_auth_number"
                            class="form-control" placeholder="Ej. AUT-1234-2024">
                        @error('form.special_auth_number') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                </div>
            </div>

            {{-- SECCIÓN 3: Mantenimiento (solo informativo, no editable) --}}
            <div class="border-t pt-4">
                <h3 class="text-sm font-semibold text-token uppercase tracking-wide mb-1">Mantenimiento</h3>
                <p class="text-xs text-gray-400 mb-4">Calculado automáticamente desde el historial de mantenimientos.</p>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    <div class="form-field">
                        <label class="form-label">Último mantenimiento</label>
                        <input type="text" class="form-control bg-surface-muted cursor-not-allowed"
                            value="{{ $lastMaintenanceDisplay }}" readonly tabindex="-1">
                    </div>

                    <div class="form-field">
                        <label class="form-label">Próximo mantenimiento</label>
                        <input type="text" class="form-control bg-surface-muted cursor-not-allowed"
                            value="{{ $nextMaintenanceDisplay }}" readonly tabindex="-1">
                    </div>

                </div>
            </div>

            {{-- SECCIÓN 4: Observaciones técnicas --}}
            <div class="border-t pt-4">
                <div class="form-field">
                    <label for="technical_details" class="form-label">Observaciones / Detalles técnicos</label>
                    <textarea id="technical_details" wire:model.defer="form.technical_details"
                        rows="3" class="form-control"
                        placeholder="Anotaciones sobre el estado del vehículo, características especiales, etc."></textarea>
                    @error('form.technical_details') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex items-center justify-end gap-3 border-t pt-4">
                <a href="{{ route('fleet.trucks.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    {{ $isEdit ? 'Actualizar camión' : 'Guardar camión' }}
                </button>
            </div>

        </form>
    </div>

    @if ($isEdit)
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-token">
                Para cumplimiento, sube el Certificado MTC como documento tipo "Certificado MTC" e ingresa su fecha de vencimiento.
            </p>
        </div>
    @endif

    <x-fleet.document-panel
        :is-edit="$isEdit"
        documentable-type="truck"
        :documentable-id="$truck->id ?? null"
        pending-message="Guarda el camión para habilitar la carga del Certificado MTC (PDF/imagen), SOAT, pólizas y revisiones técnicas."
    />

    @if($isEdit)
        <div class="surface-card p-6 shadow-lg">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-token">Historial de mantenimiento</h2>
                <a href="{{ route('fleet.maintenance.create') }}" class="btn btn-primary">Programar mantenimiento</a>
            </div>

            @if(!empty($maintenanceHistory))
                <div class="mt-6 overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr class="table-row">
                                <th class="table-header">Fecha</th>
                                <th class="table-header">Tipo</th>
                                <th class="table-header">Estado</th>
                                <th class="table-header">Costo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($maintenanceHistory as $history)
                                @php($status = $maintenanceStatusTags[$history['status']] ?? $maintenanceStatusTags['scheduled'])
                                <tr class="table-row table-row-hover">
                                    <td class="table-cell text-token">{{ $history['date'] }}</td>
                                    <td class="table-cell text-token">{{ $history['type'] }}</td>
                                    <td class="table-cell">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $status['class'] }}">
                                            {{ $status['label'] }}
                                        </span>
                                    </td>
                                    <td class="table-cell text-token">
                                        {{ $history['cost'] !== null ? \App\Support\Formatters\MoneyFormatter::pen((float) $history['cost']) : 'Sin costo' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mt-6 text-sm text-token">Sin registros de mantenimiento para este vehículo.</p>
            @endif
        </div>
    @endif

</div>
