<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <div class="space-y-1">
 <h1 class="text-2xl font-semibold text-slate-900 ">Gestion de Camiones</h1>
 <p class="text-sm text-slate-500 ">Monitorea disponibilidad, mantenimientos y asignaciones de la flota.</p>
 </div>
 <a href="{{ route('fleet.trucks.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 ">
 <span class="text-lg leading-none">+</span>
 Agregar Camion
 </a>
 </div>

 @if (session()->has('message'))
 <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm ">
 {{ session('message') }}
 </div>
 @endif

 <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">Camiones disponibles</p>
 <p class="mt-1 text-2xl font-semibold text-emerald-600 ">{{ $statusTotals['available'] ?? 0 }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">Camiones en uso</p>
 <p class="mt-1 text-2xl font-semibold text-sky-600 ">{{ $statusTotals['in_use'] ?? 0 }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">En mantenimiento</p>
 <p class="mt-1 text-2xl font-semibold text-amber-600 ">{{ $statusTotals['maintenance'] ?? 0 }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">Mantenimientos proximos (30 dias)</p>
 <p class="mt-1 text-2xl font-semibold text-rose-600 ">{{ $maintenanceDueSoon }}</p>
 </div>
 </div>

 <div class="surface-card shadow-lg">
 <div class="flex flex-col gap-4 border-b border-slate-200 px-4 py-4 md:flex-row md:items-center md:justify-between">
 <div class="w-full md:max-w-md">
 <label class="sr-only" for="truck-search">Buscar</label>
 <input id="truck-search" wire:model.live="search" type="text" placeholder="Buscar por placa, marca o modelo..." class="form-control">
 </div>
 <div class="flex flex-wrap items-center gap-3">
 <select wire:model.live="status" class="form-control md:w-44">
 <option value="">Todos los estados</option>
 <option value="available">Disponible</option>
 <option value="in_use">En uso</option>
 <option value="maintenance">En mantenimiento</option>
 <option value="out_of_service">Fuera de servicio</option>
 </select>
 <button type="button" wire:click="$set('status', '')" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 ">
 Limpiar
 </button>
 </div>
 </div>

 <div class="overflow-x-auto">
 <table class="surface-table">
 <thead>
 <tr>
 <th class="px-6 py-3">Placa</th>
 <th class="px-6 py-3">Marca/Modelo</th>
 <th class="px-6 py-3">Ano</th>
 <th class="px-6 py-3">Tipo</th>
 <th class="px-6 py-3">Kilometraje</th>
 <th class="px-6 py-3">Estado</th>
 <th class="px-6 py-3">Prox. Mant.</th>
 <th class="px-6 py-3">Alerta</th>
 <th class="px-6 py-3 text-center">Pend. Mant.</th>
 <th class="px-6 py-3 text-right">Acciones</th>
 </tr>
 </thead>
 <tbody>
 @forelse ($trucks as $truck)
 @php
 $statusStyles = [
 'available' => ['label' => 'Disponible', 'class' => 'bg-emerald-100 text-emerald-700 '],
 'in_use' => ['label' => 'En uso', 'class' => 'bg-sky-100 text-sky-700 '],
 'maintenance' => ['label' => 'En mantenimiento', 'class' => 'bg-amber-100 text-amber-700 '],
 'out_of_service' => ['label' => 'Fuera de servicio', 'class' => 'bg-rose-100 text-rose-700 '],
 ];
 $statusConfig = $statusStyles[$truck->status] ?? $statusStyles['available'];
 $nextMaintenance = $truck->next_maintenance;
 $isPastDue = $nextMaintenance && $nextMaintenance->isPast();
 $isDueSoon = $nextMaintenance && !$isPastDue && $nextMaintenance->lessThanOrEqualTo(now()->addDays(30));
 $nextClass = $isPastDue
 ? 'text-rose-600 font-semibold '
 : ($isDueSoon
 ? 'text-amber-600 font-semibold '
 : 'text-slate-700 ');
 $alertLevel = $truck->maintenanceAlertLevel();
 @endphp
 <tr class="transition hover:bg-slate-100 ">
 <td class="px-6 py-3 font-medium text-slate-900 ">{{ $truck->plate_number }}</td>
 <td class="px-6 py-3 text-slate-600 ">{{ $truck->brand }} {{ $truck->model }}</td>
 <td class="px-6 py-3 text-slate-600 ">{{ $truck->year }}</td>
 <td class="px-6 py-3 text-slate-600 ">{{ $truck->type }}</td>
 <td class="px-6 py-3 text-slate-600 ">{{ number_format($truck->mileage ?? 0) }} km</td>
 <td class="px-6 py-3">
 <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusConfig['class'] }}">
 {{ $statusConfig['label'] }}
 </span>
 </td>
 <td class="px-6 py-3 {{ $nextClass }}">
 {{ $nextMaintenance ? $nextMaintenance->format('d/m/Y') : 'No programado' }}
 </td>
 <td class="px-6 py-3">
 <span @class([
 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold',
 'bg-emerald-100 text-emerald-700 ' => $alertLevel === 'ok',
 'bg-amber-100 text-amber-700 ' => $alertLevel === 'warning',
 'bg-rose-100 text-rose-700 ' => $alertLevel === 'danger',
 ])>
 @switch($alertLevel)
 @case('danger') Requiere mantenimiento @break
 @case('warning') Revisar pronto @break
 @default Sin alertas
 @endswitch
 </span>
 </td>
 <td class="px-6 py-3 text-center text-slate-600 ">{{ $truck->pending_maintenances_count ?? 0 }}</td>
 <td class="px-6 py-3 text-right">
 <a href="{{ route('fleet.trucks.edit', $truck) }}" class="font-semibold text-indigo-600 transition hover:text-indigo-700 ">Editar</a>
 <button wire:click="deleteTruck({{ $truck->id }})" wire:confirm="Esta seguro de eliminar este camion?" class="ml-3 font-semibold text-rose-600 transition hover:text-rose-700 ">Eliminar</button>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="10" class="px-6 py-6 text-center text-slate-500 ">No hay camiones registrados.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 <div class="border-t border-slate-200 px-4 py-3 ">
 {{ $trucks->links() }}
 </div>
 </div>
</div>
