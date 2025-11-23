<div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h1 class="text-2xl font-semibold text-token ">{{ $isEdit ? 'Editar Camion' : 'Registrar Camion' }}</h1>
    <a href="{{ route('fleet.trucks.index') }}" class="btn btn-secondary">
        Volver
    </a>
 </div>

 <div class="surface-card p-6 shadow-lg">
 <form wire:submit.prevent="save" class="grid gap-6">
 <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
 <div class="form-field">
 <label for="plate_number" class="form-label">Placa</label>
 <input type="text" id="plate_number" wire:model.defer="form.plate_number" class="form-control">
 @error('form.plate_number') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="brand" class="form-label">Marca</label>
 <input type="text" id="brand" wire:model.defer="form.brand" class="form-control">
 @error('form.brand') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="model" class="form-label">Modelo</label>
 <input type="text" id="model" wire:model.defer="form.model" class="form-control">
 @error('form.model') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="year" class="form-label">Ano</label>
 <input type="number" id="year" wire:model.defer="form.year" class="form-control">
 @error('form.year') <span class="form-error">{{ $message }}</span> @enderror
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
 @error('form.type') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="capacity" class="form-label">Capacidad (Ton)</label>
 <input type="number" step="0.01" id="capacity" wire:model.defer="form.capacity" class="form-control">
 @error('form.capacity') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="mileage" class="form-label">Kilometraje</label>
 <input type="number" id="mileage" wire:model.defer="form.mileage" class="form-control">
 @error('form.mileage') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="status" class="form-label">Estado</label>
 <select id="status" wire:model.defer="form.status" class="form-control">
 @foreach($statusLabels as $value => $label)
 <option value="{{ $value }}">{{ $label }}</option>
 @endforeach
 </select>
 @error('form.status') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="last_maintenance" class="form-label">Ultimo mantenimiento</label>
 <input type="date" id="last_maintenance" wire:model.defer="form.last_maintenance" class="form-control">
 @error('form.last_maintenance') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="next_maintenance" class="form-label">Proximo mantenimiento</label>
 <input type="date" id="next_maintenance" wire:model.defer="form.next_maintenance" class="form-control">
 @error('form.next_maintenance') <span class="form-error">{{ $message }}</span> @enderror
 </div>
 </div>

 <div class="form-field">
 <label for="technical_details" class="form-label">Detalles tecnicos</label>
 <textarea id="technical_details" wire:model.defer="form.technical_details" rows="4" class="form-control"></textarea>
 @error('form.technical_details') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="flex items-center justify-end gap-3">
    <a href="{{ route('fleet.trucks.index') }}" class="btn btn-secondary">
        Cancelar
    </a>
    <button type="submit" class="btn btn-primary">
        {{ $isEdit ? 'Actualizar' : 'Guardar' }}
    </button>
 </div>
 </form>
 </div>

 <x-fleet.document-panel
 :is-edit="$isEdit"
 documentable-type="truck"
 :documentable-id="$truck->id ?? null"
 pending-message="{{ $isEdit
 ? 'Guarda el camión para poder adjuntar pólizas, SOAT u otros documentos.'
 : 'Guarda el camión para habilitar la carga de documentos (SOAT, pólizas, revisiones técnicas).'
 }}"
 />

 @if($isEdit)
 <div class="surface-card p-6 shadow-lg">
 <div class="flex flex-wrap items-center justify-between gap-3">
 <h2 class="text-lg font-semibold text-token ">Historial de mantenimiento</h2>
    <a href="{{ route('fleet.maintenance.create') }}" class="btn btn-primary">
        Programar mantenimiento
    </a>
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
              <td class="table-cell text-token ">{{ $history['date'] }}</td>
              <td class="table-cell text-token ">{{ $history['type'] }}</td>
              <td class="table-cell">
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $status['class'] }}">
                  {{ $status['label'] }}
                </span>
              </td>
              <td class="table-cell text-token ">
                {{ $history['cost'] !== null ? \App\Support\Formatters\MoneyFormatter::pen((float) $history['cost']) : 'Sin costo' }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
 @else
 <p class="mt-6 text-sm text-token ">Sin registros de mantenimiento para este vehiculo.</p>
 @endif
 </div>
 @endif

</div>
