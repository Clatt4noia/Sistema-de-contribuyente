<div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <div>
 <h1 class="text-2xl font-semibold text-slate-900 ">{{ $isEdit ? 'Editar Chofer' : 'Registrar Chofer' }}</h1>

 @if ($form['license_expiration'])
 @php
 $expiresAt = \Illuminate\Support\Carbon::parse($form['license_expiration']);
 $daysLeft = now()->diffInDays($expiresAt, false);
 @endphp

 <p class="mt-2 text-sm text-slate-600 ">
 <span class="font-medium">Vigencia de licencia:</span>
 <span
 @class([
 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold shadow-sm transition',
 'bg-red-100 text-red-700 ' => $daysLeft < 0,
 'bg-yellow-100 text-yellow-700 ' => $daysLeft >= 0 && $daysLeft <= 30,
 'bg-green-100 text-green-700 ' => $daysLeft > 30,
 ])
 >

 {{ $expiresAt->format('d/m/Y') }}
 ({{ $daysLeft < 0 ? 'Vencida' : 'Quedan ' . $daysLeft . ' días' }})
 </span>
 </p>
 @endif
 </div>

 <a
 href="{{ route('fleet.drivers.index') }}"
 class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 "
 >

 Volver
 </a>
 </div>

 <div class="surface-card space-y-8 p-6">
 <form wire:submit.prevent="save" class="space-y-8">
 <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
 <div class="form-field">
 <label for="name" class="form-label">Nombre *</label>
 <input type="text" id="name" wire:model.defer="form.name" class="form-control">
 @error('form.name') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="last_name" class="form-label">Apellido *</label>
 <input type="text" id="last_name" wire:model.defer="form.last_name" class="form-control">
 @error('form.last_name') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="document_number" class="form-label">Número de documento *</label>
 <input type="text" id="document_number" wire:model.defer="form.document_number" class="form-control">
 @error('form.document_number') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="license_number" class="form-label">Número de licencia *</label>
 <input type="text" id="license_number" wire:model.defer="form.license_number" class="form-control">
 @error('form.license_number') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="license_expiration" class="form-label">Vencimiento de licencia *</label>
 <input type="date" id="license_expiration" wire:model.defer="form.license_expiration" class="form-control">
 @error('form.license_expiration') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="phone" class="form-label">Teléfono *</label>
 <input type="text" id="phone" wire:model.defer="form.phone" class="form-control">
 @error('form.phone') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="email" class="form-label">Email *</label>
 <input type="email" id="email" wire:model.defer="form.email" class="form-control">
 @error('form.email') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="status" class="form-label">Estado *</label>
 <select id="status" wire:model.defer="form.status" class="form-control">
 <option value="active">Activo</option>
 <option value="inactive">Inactivo</option>
 <option value="on_leave">De permiso</option>
 <option value="assigned">Asignado</option>
 </select>
 @error('form.status') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 </div>

 <div class="form-field md:col-span-2">
 <label for="address" class="form-label">Dirección *</label>
 <input type="text" id="address" wire:model.defer="form.address" class="form-control">
 @error('form.address') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field md:col-span-2">
 <label for="notes" class="form-label">Notas</label>
 <textarea id="notes" wire:model.defer="form.notes" rows="4" class="form-control"></textarea>
 @error('form.notes') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="space-y-6 border-t border-slate-200 pt-6 ">
 <div class="flex flex-wrap items-center justify-between gap-3">
 <h2 class="text-lg font-semibold text-slate-900 ">Horarios</h2>
 <button
 type="button"
 wire:click="addSchedule"
 class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 "
 >
 Agregar horario
 </button>
 </div>

 @error('schedules')
 <div class="text-sm font-medium text-rose-500">{{ $message }}</div>
 @enderror

 <div class="space-y-4">
 @forelse ($schedules as $index => $schedule)
 <div
 wire:key="schedule-{{ $index }}"
 class="grid grid-cols-1 items-end gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 transition md:grid-cols-4"
 >
 <div class="space-y-2">
 <label class="text-xs font-medium text-slate-600 ">Día</label>
 <select wire:model.defer="schedules.{{ $index }}.day_of_week" class="form-control text-sm">
 <option value="Lunes">Lunes</option>
 <option value="Martes">Martes</option>
 <option value="Miercoles">Miércoles</option>
 <option value="Jueves">Jueves</option>
 <option value="Viernes">Viernes</option>
 <option value="Sabado">Sábado</option>
 <option value="Domingo">Domingo</option>
 </select>
 @error('schedules.' . $index . '.day_of_week') <span class="text-xs font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-2">
 <label class="text-xs font-medium text-slate-600 ">Inicio</label>
 <input type="time" wire:model.defer="schedules.{{ $index }}.start_time" class="form-control text-sm">
 @error('schedules.' . $index . '.start_time') <span class="text-xs font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-2">
 <label class="text-xs font-medium text-slate-600 ">Fin</label>
 <input type="time" wire:model.defer="schedules.{{ $index }}.end_time" class="form-control text-sm">
 @error('schedules.' . $index . '.end_time') <span class="text-xs font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 <div class="flex justify-end">
 <button
 type="button"
 wire:click="removeSchedule({{ $index }})"
 class="inline-flex items-center gap-2 rounded-xl bg-rose-500 px-3 py-2 text-sm font-semibold text-white shadow transition hover:bg-rose-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500"
 >
 Eliminar
 </button>
 </div>
 </div>
 @empty
 <p class="text-sm text-slate-500 ">No se han definido horarios. Agrega al menos uno para planificar disponibilidad.</p>
 @endforelse
 </div>
 </div>

 <div class="space-y-6 border-t border-slate-200 pt-6 ">
 <div class="flex flex-wrap items-center justify-between gap-3">
 <h2 class="text-lg font-semibold text-slate-900 ">Capacitaciones</h2>
 <button
 type="button"
 wire:click="addTraining"
 class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 "
 >
 Agregar capacitación
 </button>

 </div>

 <div class="space-y-4">
 @forelse ($trainings as $index => $training)
 <div
 wire:key="training-{{ $index }}"
 class="grid grid-cols-1 gap-4 rounded-2xl border border-indigo-200 bg-indigo-50 p-4 transition md:grid-cols-6"
 >
 <div class="md:col-span-2 space-y-2">
 <label class="text-xs font-medium text-slate-600 ">Nombre *</label>
 <input type="text" wire:model.defer="trainings.{{ $index }}.name" class="form-control text-sm">
 @error('trainings.' . $index . '.name') <span class="text-xs font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-2">
 <label class="text-xs font-medium text-slate-600 ">Proveedor</label>
 <input type="text" wire:model.defer="trainings.{{ $index }}.provider" class="form-control text-sm">
 @error('trainings.' . $index . '.provider') <span class="text-xs font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-2">
 <label class="text-xs font-medium text-slate-600 ">Emitida</label>
 <input type="date" wire:model.defer="trainings.{{ $index }}.issued_at" class="form-control text-sm">
 @error('trainings.' . $index . '.issued_at') <span class="text-xs font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-2">
 <label class="text-xs font-medium text-slate-600 ">Vence</label>
 <input type="date" wire:model.defer="trainings.{{ $index }}.expires_at" class="form-control text-sm">
 @error('trainings.' . $index . '.expires_at') <span class="text-xs font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-2">
 <label class="text-xs font-medium text-slate-600 ">Horas</label>
 <input type="number" min="0" wire:model.defer="trainings.{{ $index }}.hours" class="form-control text-sm">
 @error('trainings.' . $index . '.hours') <span class="text-xs font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 <div class="space-y-2">
 <label class="text-xs font-medium text-slate-600 ">Estado</label>
 <select wire:model.defer="trainings.{{ $index }}.status" class="form-control text-sm">
 <option value="valid">Vigente</option>
 <option value="in_progress">En curso</option>
 <option value="expired">Vencida</option>
 </select>
 @error('trainings.' . $index . '.status') <span class="text-xs font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 <div class="md:col-span-2 space-y-2">
 <label class="text-xs font-medium text-slate-600 ">Certificado (URL)</label>
 <input type="url" wire:model.defer="trainings.{{ $index }}.certificate_url" class="form-control text-sm">
 @error('trainings.' . $index . '.certificate_url') <span class="text-xs font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 <div class="flex justify-end md:col-span-6">
 <button
 type="button"
 wire:click="removeTraining({{ $index }})"
 class="inline-flex items-center gap-2 rounded-xl bg-rose-500 px-3 py-2 text-sm font-semibold text-white shadow transition hover:bg-rose-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500"
 >
 Eliminar
 </button>
 </div>
 </div>
 @empty
 <p class="text-sm text-slate-500 ">No se han registrado capacitaciones.</p>
 @endforelse
 </div>
 </div>

 <div class="flex flex-wrap items-center justify-end gap-3">
 <a
 href="{{ route('fleet.drivers.index') }}"
 class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 "
 >
 Cancelar
 </a>
 <button
 type="submit"
 class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 "
 >
 {{ $isEdit ? 'Actualizar' : 'Guardar' }}
 </button>
 </div>
 </form>
 </div>

 @if ($isEdit)
 @if ($isEdit && $driver->exists)
 <livewire:fleet.document-manager
 :documentable-type="'driver'"
 :documentable-id="$driver->id"
 :key="'driver-documents-' . $driver->id"
 />
 @else
 <div class="surface-card border border-dashed border-slate-300 p-6 text-sm text-slate-600 ">
 Guarda el chofer para habilitar la carga de documentos (licencia, certificados, etc.).
 </div>
 @endif

 @else
 <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-6 text-sm text-slate-500 ">
 Guarda el registro del chofer para adjuntar licencias escaneadas, certificados médicos o constancias de capacitación.
 </div>
 @endif
</div>
