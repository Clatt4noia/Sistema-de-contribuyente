<div class="space-y-6">
 <div class="flex flex-wrap items-center justify-between gap-3">
 <div>
 <h2 class="text-lg font-semibold text-slate-900 ">Rutas adicionales</h2>
 <p class="text-sm text-slate-500 ">Documenta alternativas, desvíos y datos complementarios al plan principal.</p>
 </div>

 @if (session()->has('message'))
 <span class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 ">{{ session('message') }}</span>
 @endif
 </div>

 <form wire:submit="save" class="grid grid-cols-1 gap-4 md:grid-cols-2">
 <div class="form-field">
 <label for="planner" class="form-label">Planificador</label>
 <input id="planner" type="text" wire:model.defer="planner" class="form-control" placeholder="Operador a cargo">
 @error('planner') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label for="map_url" class="form-label">URL del mapa</label>
 <input id="map_url" type="url" wire:model.defer="map_url" class="form-control" placeholder="https://maps...">
 @error('map_url') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field md:col-span-2">
 <label for="route_summary" class="form-label">Resumen *</label>
 <textarea id="route_summary" rows="3" wire:model.defer="route_summary" class="form-control" placeholder="Puntos clave, carreteras, riesgos..."></textarea>
 @error('route_summary') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="form-field md:col-span-2">
 <label for="route_data" class="form-label">Datos JSON</label>
 <textarea id="route_data" rows="3" wire:model.defer="route_data" class="form-control" placeholder='{"waypoints": []}'></textarea>
 @error('route_data') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>

 <div class="md:col-span-2 flex justify-end">
 <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 ">
 Agregar ruta
 </button>
 </div>
 </form>

 <div class="rounded-2xl border border-slate-200 shadow-sm ">
 <table class="surface-table">
 <thead>
 <tr>
 <th class="px-4 py-2">Planificador</th>
 <th class="px-4 py-2">Resumen</th>
 <th class="px-4 py-2">Mapa</th>
 <th class="px-4 py-2">Acciones</th>
 </tr>
 </thead>
 <tbody>
 @forelse($plans as $plan)
 <tr class="transition hover:bg-slate-100 ">
 <td class="px-4 py-2 text-sm text-slate-700 ">{{ $plan->planner ?: 'No definido' }}</td>
 <td class="px-4 py-2 text-sm text-slate-700 ">{{ \Illuminate\Support\Str::limit($plan->route_summary, 80) }}</td>
 <td class="px-4 py-2 text-sm">
 @if($plan->map_url)
 <a href="{{ $plan->map_url }}" target="_blank" class="font-medium text-indigo-600 transition hover:text-indigo-700 ">Ver mapa</a>
 @else
 <span class="text-sm text-slate-400 ">Sin enlace</span>
 @endif
 </td>
 <td class="px-4 py-2 text-sm">
 <button wire:click="delete({{ $plan->id }})" wire:confirm="Eliminar esta ruta?" class="font-semibold text-rose-600 transition hover:text-rose-700 ">Eliminar</button>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="px-4 py-3 text-center text-sm text-slate-500 ">No hay rutas adicionales registradas.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
</div>
