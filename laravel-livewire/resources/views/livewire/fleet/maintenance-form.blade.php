<div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h1 class="text-2xl font-semibold text-slate-900 ">{{ $isEdit ? 'Editar Mantenimiento' : 'Registrar Mantenimiento' }}</h1>
    <a
        href="{{ route('fleet.maintenance.index') }}"
        class="btn btn-secondary"
    >
        Volver
    </a>
 </div>

 <div class="surface-card p-6">
 <form wire:submit.prevent="save" class="space-y-6">
 <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
 <div class="form-field">
 <label for="truck_id" class="form-label">Vehículo *</label>
 <select id="truck_id" wire:model="form.truck_id" class="form-control">
 <option value="">Seleccione un vehículo</option>
 @foreach($trucks as $truck)
 <option value="{{ $truck->id }}">{{ $truck->plate_number }} - {{ $truck->brand }} {{ $truck->model }}</option>
 @endforeach
 </select>
 @error('form.truck_id') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="maintenance_date" class="form-label">Fecha de mantenimiento *</label>
 <input type="date" id="maintenance_date" wire:model="form.maintenance_date" class="form-control">
 @error('form.maintenance_date') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="maintenance_type" class="form-label">Tipo de mantenimiento *</label>
 <select id="maintenance_type" wire:model="form.maintenance_type" class="form-control">
 <option value="">Seleccione un tipo</option>
 <option value="Preventivo">Preventivo</option>
 <option value="Correctivo">Correctivo</option>
 <option value="Revisión">Revisión</option>
 <option value="Cambio de aceite">Cambio de aceite</option>
 <option value="Cambio de filtros">Cambio de filtros</option>
 <option value="Cambio de neumáticos">Cambio de neumáticos</option>
 <option value="Reparación">Reparación</option>
 <option value="Otro">Otro</option>
 </select>
 @error('form.maintenance_type') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="cost" class="form-label">Costo *</label>
 <input type="number" step="0.01" id="cost" wire:model="form.cost" class="form-control">
 @error('form.cost') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="odometer" class="form-label">Odómetro (km)</label>
 <input type="number" id="odometer" wire:model="form.odometer" class="form-control" placeholder="{{ __('Lectura actual del vehículo') }}">
 @error('form.odometer') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="status" class="form-label">Estado *</label>
 <select id="status" wire:model="form.status" class="form-control">

 <option value="scheduled">Programado</option>
 <option value="in_progress">En progreso</option>
 <option value="completed">Completado</option>
 <option value="cancelled">Cancelado</option>
 </select>
 @error('form.status') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 </div>

 <div class="form-field">
 <label for="description" class="form-label">Descripción</label>
 <textarea id="description" wire:model="form.description" rows="3" class="form-control"></textarea>
 @error('form.description') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="notes" class="form-label">Notas adicionales</label>
 <textarea id="notes" wire:model="form.notes" rows="3" class="form-control"></textarea>
 @error('form.notes') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="flex flex-wrap items-center justify-end gap-3">
    <a
        href="{{ route('fleet.maintenance.index') }}"
        class="btn btn-secondary"
    >
        Cancelar
    </a>
    <button
        type="submit"
        class="btn btn-primary"
    >
        {{ $isEdit ? 'Actualizar' : 'Guardar' }}
    </button>
 </div>
 </form>
 </div>
</div>
