<div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-token">{{ $isEdit ? 'Editar Chofer' : 'Registrar Chofer' }}</h1>
            @if ($this->licenseValidity)
                <p class="mt-2 text-sm text-token">
                    <span class="font-medium">Vigencia de licencia:</span>
                    <span @class([
                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold shadow-sm border',
                        $this->licenseValidity['status_class'],
                    ])>
                        {{ $this->licenseValidity['formatted_date'] }}
                        ({{ $this->licenseValidity['status_label'] }})
                    </span>
                </p>
            @endif
        </div>
        <a href="{{ route('fleet.drivers.index') }}" class="btn btn-secondary">Volver</a>
    </div>

    <div class="surface-card p-6 shadow-lg">
        <form wire:submit.prevent="save" class="space-y-8">

            {{-- SECCIÓN 1: Datos personales --}}
            <div>
                <h3 class="text-sm font-semibold text-token uppercase tracking-wide mb-4">Datos personales</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    <div class="form-field">
                        <label for="name" class="form-label">Nombres <span class="text-danger-strong">*</span></label>
                        <input type="text" id="name" wire:model.defer="form.name"
                            class="form-control" placeholder="Ej. Juan Carlos">
                        @error('form.name') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="last_name" class="form-label">Apellidos <span class="text-danger-strong">*</span></label>
                        <input type="text" id="last_name" wire:model.defer="form.last_name"
                            class="form-control" placeholder="Ej. Ramirez Torres">
                        @error('form.last_name') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="document_type" class="form-label">Tipo de documento <span class="text-danger-strong">*</span></label>
                        <select id="document_type" wire:model.defer="form.document_type" class="form-control">
                            <option value="1">DNI</option>
                            <option value="4">Carné de Extranjería</option>
                            <option value="7">Pasaporte</option>
                        </select>
                        @error('form.document_type') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="document_number" class="form-label">Número de documento <span class="text-danger-strong">*</span></label>
                        <input type="text" id="document_number" wire:model.defer="form.document_number"
                            class="form-control" placeholder="Ej. 45678901" maxlength="20">
                        @error('form.document_number') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="phone" class="form-label">Teléfono / Celular</label>
                        <input type="text" id="phone" wire:model.defer="form.phone"
                            class="form-control" placeholder="Ej. 987654321">
                        @error('form.phone') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" id="email" wire:model.defer="form.email"
                            class="form-control" placeholder="chofer@ejemplo.com">
                        @error('form.email') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field md:col-span-2">
                        <label for="address" class="form-label">Dirección</label>
                        <input type="text" id="address" wire:model.defer="form.address"
                            class="form-control" placeholder="Ej. Av. Los Olivos 123, Lima">
                        @error('form.address') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                </div>
            </div>

            {{-- SECCIÓN 2: Licencia de conducir --}}
            <div class="border-t pt-6">
                <h3 class="text-sm font-semibold text-token uppercase tracking-wide mb-4">Licencia de conducir</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    <div class="form-field">
                        <label for="license_number" class="form-label">N° de licencia <span class="text-danger-strong">*</span></label>
                        <input type="text" id="license_number" wire:model.defer="form.license_number"
                            class="form-control" placeholder="Ej. Q64300015">
                        @error('form.license_number') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="license_category" class="form-label">Categoría de licencia</label>
                        <select id="license_category" wire:model.defer="form.license_category" class="form-control">
                            <option value="">Seleccione categoría</option>
                            <optgroup label="Vehículos de transporte de mercancías">
                                <option value="A-IIb">A-IIb — Camión (hasta 2 ejes)</option>
                                <option value="A-IIIa">A-IIIa — Camión pesado (más de 2 ejes / buses)</option>
                                <option value="A-IIIb">A-IIIb — Vehículo articulado (tracto + semirremolque)</option>
                            </optgroup>
                            <optgroup label="Otras categorías">
                                <option value="A-I">A-I — Mototaxi / moto</option>
                                <option value="A-IIa">A-IIa — Combi / minibús</option>
                                <option value="B-I">B-I — Auto particular</option>
                                <option value="B-IIa">B-IIa — Camioneta</option>
                                <option value="B-IIb">B-IIb — Furgoneta</option>
                                <option value="B-IIc">B-IIc — Remolque ligero</option>
                            </optgroup>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Categoría MTC. Para tractos: A-IIIb. Para camiones: A-IIb o A-IIIa.</p>
                        @error('form.license_category') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="license_expiration" class="form-label">Vencimiento de licencia <span class="text-danger-strong">*</span></label>
                        <input type="date" id="license_expiration" wire:model.live="form.license_expiration"
                            class="form-control">
                        @error('form.license_expiration') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-field">
                        <label for="status" class="form-label">Estado <span class="text-danger-strong">*</span></label>
                        <select id="status" wire:model.defer="form.status" class="form-control">
                            <option value="active">Activo</option>
                            <option value="inactive">Inactivo</option>
                            <option value="on_leave">De permiso</option>
                            <option value="assigned">Asignado</option>
                        </select>
                        @error('form.status') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                </div>
            </div>

            {{-- SECCIÓN 3: Horarios --}}
            <div class="border-t pt-6">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h3 class="text-sm font-semibold text-token uppercase tracking-wide">Horarios de trabajo</h3>
                    <button type="button" wire:click="addSchedule" class="btn btn-primary btn-sm">
                        + Agregar horario
                    </button>
                </div>

                @error('schedules')
                    <div class="form-error mb-3">{{ $message }}</div>
                @enderror

                <div class="space-y-3">
                    @forelse ($schedules as $index => $schedule)
                        <div wire:key="schedule-{{ $index }}"
                            class="grid grid-cols-1 items-end gap-4 rounded-xl border border-token bg-surface p-4 md:grid-cols-4">
                            <div class="space-y-2">
                                <label class="text-xs font-medium text-token">Día</label>
                                <select wire:model.defer="schedules.{{ $index }}.day_of_week" class="form-control text-sm">
                                    <option value="Lunes">Lunes</option>
                                    <option value="Martes">Martes</option>
                                    <option value="Miercoles">Miércoles</option>
                                    <option value="Jueves">Jueves</option>
                                    <option value="Viernes">Viernes</option>
                                    <option value="Sabado">Sábado</option>
                                    <option value="Domingo">Domingo</option>
                                </select>
                                @error('schedules.' . $index . '.day_of_week') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-medium text-token">Inicio</label>
                                <input type="time" wire:model.defer="schedules.{{ $index }}.start_time" class="form-control text-sm">
                                @error('schedules.' . $index . '.start_time') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-medium text-token">Fin</label>
                                <input type="time" wire:model.defer="schedules.{{ $index }}.end_time" class="form-control text-sm">
                                @error('schedules.' . $index . '.end_time') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex justify-end">
                                <button type="button" wire:click="removeSchedule({{ $index }})" class="btn btn-danger btn-sm">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-token/70 italic">No se han definido horarios. Agrega al menos uno para planificar disponibilidad.</p>
                    @endforelse
                </div>
            </div>

            {{-- SECCIÓN 4: Capacitaciones --}}
            <div class="border-t pt-6">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h3 class="text-sm font-semibold text-token uppercase tracking-wide">Capacitaciones</h3>
                    <button type="button" wire:click="addTraining" class="btn btn-primary btn-sm">
                        + Agregar capacitación
                    </button>
                </div>

                <div class="space-y-3">
                    @forelse ($trainings as $index => $training)
                        <div wire:key="training-{{ $index }}"
                            class="rounded-xl border border-accent-soft bg-accent-soft/30 p-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="space-y-2 md:col-span-2">
                                    <label class="text-xs font-medium text-token">Nombre de la capacitación <span class="text-danger-strong">*</span></label>
                                    <input type="text" wire:model.defer="trainings.{{ $index }}.name" class="form-control text-sm"
                                        placeholder="Ej. Manejo defensivo, Transporte de mercancías peligrosas">
                                    @error('trainings.' . $index . '.name') <span class="form-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-medium text-token">Proveedor</label>
                                    <input type="text" wire:model.defer="trainings.{{ $index }}.provider" class="form-control text-sm"
                                        placeholder="Ej. SENATI, MTC">
                                    @error('trainings.' . $index . '.provider') <span class="form-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-medium text-token">Fecha de emisión</label>
                                    <input type="date" wire:model.defer="trainings.{{ $index }}.issued_at" class="form-control text-sm">
                                    @error('trainings.' . $index . '.issued_at') <span class="form-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-medium text-token">Fecha de vencimiento</label>
                                    <input type="date" wire:model.defer="trainings.{{ $index }}.expires_at" class="form-control text-sm">
                                    @error('trainings.' . $index . '.expires_at') <span class="form-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-medium text-token">Horas</label>
                                    <input type="number" min="0" wire:model.defer="trainings.{{ $index }}.hours" class="form-control text-sm"
                                        placeholder="Ej. 8">
                                    @error('trainings.' . $index . '.hours') <span class="form-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-medium text-token">Estado</label>
                                    <select wire:model.defer="trainings.{{ $index }}.status" class="form-control text-sm">
                                        <option value="valid">Vigente</option>
                                        <option value="in_progress">En curso</option>
                                        <option value="expired">Vencida</option>
                                    </select>
                                    @error('trainings.' . $index . '.status') <span class="form-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="mt-3 flex justify-end">
                                <button type="button" wire:click="removeTraining({{ $index }})" class="btn btn-danger btn-sm">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-token/70 italic">No se han registrado capacitaciones.</p>
                    @endforelse
                </div>
            </div>

            {{-- SECCIÓN 5: Notas --}}
            <div class="border-t pt-6">
                <div class="form-field">
                    <label for="notes" class="form-label">Observaciones / Notas internas</label>
                    <textarea id="notes" wire:model.defer="form.notes" rows="3" class="form-control"
                        placeholder="Anotaciones sobre el chofer, historial, observaciones, etc."></textarea>
                    @error('form.notes') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex flex-wrap items-center justify-end gap-3 border-t pt-4">
                <a href="{{ route('fleet.drivers.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    {{ $isEdit ? 'Actualizar chofer' : 'Guardar chofer' }}
                </button>
            </div>

        </form>
    </div>

    <x-fleet.document-panel
        :is-edit="$isEdit"
        documentable-type="driver"
        :documentable-id="$driver->id ?? null"
        pending-message="{{ $isEdit
            ? 'Sube documentos adicionales del chofer (licencia escaneada, certificados médicos, constancias, etc.).'
            : 'Guarda el registro del chofer para adjuntar licencias escaneadas, certificados médicos o constancias de capacitación.'
        }}"
    />

</div>
