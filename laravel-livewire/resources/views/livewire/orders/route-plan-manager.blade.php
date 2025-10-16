<div class="space-y-6">
 <div class="flex flex-wrap items-center justify-between gap-3">
 <div>
 <h2 class="text-lg font-semibold text-token ">Rutas adicionales</h2>
 <p class="text-sm text-token ">Documenta alternativas, desvíos y datos complementarios al plan principal.</p>
 </div>

 @if (session()->has('message'))
 <span class="alert alert-success">{{ session('message') }}</span>
 @endif
 </div>

 <form wire:submit="save" class="grid grid-cols-1 gap-4 md:grid-cols-2">
 <div class="form-field">
 <label for="planner" class="form-label">Planificador</label>
 <input id="planner" type="text" wire:model.defer="planner" class="form-control" placeholder="Operador a cargo">
 @error('planner') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="map_url" class="form-label">URL del mapa</label>
 <input id="map_url" type="url" wire:model.defer="map_url" class="form-control" placeholder="https://maps...">
 @error('map_url') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-field md:col-span-2">
 <label for="route_summary" class="form-label">Resumen *</label>
 <textarea id="route_summary" rows="3" wire:model.defer="route_summary" class="form-control" placeholder="Puntos clave, carreteras, riesgos..."></textarea>
 @error('route_summary') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-field md:col-span-2">
 <label for="route_data" class="form-label">Datos JSON</label>
 <textarea id="route_data" rows="3" wire:model.defer="route_data" class="form-control" placeholder='{"waypoints": []}'></textarea>
 @error('route_data') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="md:col-span-2 flex justify-end">
    <button type="submit" class="btn btn-primary">
        Agregar ruta
    </button>
 </div>
 </form>

  <div class="rounded-2xl border border-token shadow-sm ">
    <table class="table table-md">
      <thead>
        <tr class="table-row">
          <th class="table-header">Planificador</th>
          <th class="table-header">Resumen</th>
          <th class="table-header">Mapa</th>
          <th class="table-header">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($plans as $plan)
          <tr class="table-row table-row-hover">
            <td class="table-cell text-sm text-token ">{{ $plan->planner ?: 'No definido' }}</td>
            <td class="table-cell text-sm text-token ">{{ \Illuminate\Support\Str::limit($plan->route_summary, 80) }}</td>
            <td class="table-cell text-sm">
              @if($plan->map_url)
                <a href="{{ $plan->map_url }}" target="_blank" class="btn btn-secondary btn-sm">Ver mapa</a>
              @else
                <span class="text-sm text-token-muted ">Sin enlace</span>
              @endif
            </td>
            <td class="table-cell text-sm">
              <button wire:click="delete({{ $plan->id }})" wire:confirm="Eliminar esta ruta?" class="btn btn-danger btn-sm">Eliminar</button>
            </td>
          </tr>
        @empty
          <tr class="table-row">
            <td colspan="4" class="table-empty">No hay rutas adicionales registradas.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
