<div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h1 class="text-2xl font-semibold text-slate-900 ">Mantenimientos de Vehículos</h1>
 <a
 href="{{ route('fleet.maintenance.create') }}"
 class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 "
 >
 Registrar Mantenimiento
 </a>
 </div>

 @if (session()->has('message'))
 <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700 shadow-sm ">
 {{ session('message') }}
 </div>
 @endif

 <div class="surface-card overflow-hidden">
 <div class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50 p-4 md:flex-row md:items-center md:justify-between">
 <div class="flex-1 md:max-w-md">
 <input
 type="text"
 wire:model.live="search"
 placeholder="Buscar por tipo o descripción..."
 class="form-control"
 >
 </div>
 <div class="flex flex-wrap gap-3 text-sm">
 <select wire:model.live="truck_id" class="form-control">
 <option value="">Todos los vehículos</option>
 @foreach($trucks as $truck)
 <option value="{{ $truck->id }}">{{ $truck->plate_number }} - {{ $truck->brand }} {{ $truck->model }}</option>
 @endforeach
 </select>
 <select wire:model.live="status" class="form-control">
 <option value="">Todos los estados</option>
 <option value="scheduled">Programado</option>
 <option value="in_progress">En progreso</option>
 <option value="completed">Completado</option>
 <option value="cancelled">Cancelado</option>
 </select>
 </div>
 </div>

 <div class="overflow-x-auto">
 <table class="surface-table">
 <thead>
 <tr>
 <th class="px-6 py-3">Vehículo</th>
 <th class="px-6 py-3">Fecha</th>
 <th class="px-6 py-3">Tipo</th>
 <th class="px-6 py-3">Costo</th>
 <th class="px-6 py-3">Estado</th>
 <th class="px-6 py-3">Acciones</th>
 </tr>
 </thead>
 <tbody>
 @forelse ($maintenances as $maintenance)
 <tr class="transition hover:bg-slate-100 ">
 <td class="px-6 py-4">
 <div class="text-sm font-semibold text-slate-800 ">{{ $maintenance->truck->plate_number }}</div>
 <div class="text-xs text-slate-500 ">{{ $maintenance->truck->brand }} {{ $maintenance->truck->model }}</div>
 </td>
 <td class="px-6 py-4 text-sm text-slate-600 ">
 {{ $maintenance->maintenance_date->format('d/m/Y') }}
 </td>
 <td class="px-6 py-4 text-sm text-slate-600 ">
 {{ $maintenance->maintenance_type }}
 </td>
 <td class="px-6 py-4 text-sm text-slate-600 ">
 {{ \App\Support\Formatters\MoneyFormatter::pen($maintenance->cost) }}
 </td>
 <td class="px-6 py-4">
 <span
 @class([
 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold shadow-sm transition',
 'bg-emerald-100 text-emerald-700 ' => $maintenance->status === 'completed',
 'bg-sky-100 text-sky-700 ' => $maintenance->status === 'in_progress',
 'bg-amber-100 text-amber-700 ' => $maintenance->status === 'scheduled',
 'bg-rose-100 text-rose-700 ' => $maintenance->status === 'cancelled',
 ])
 >
 {{ $maintenance->status === 'completed' ? 'Completado' : ($maintenance->status === 'in_progress' ? 'En progreso' : ($maintenance->status === 'scheduled' ? 'Programado' : 'Cancelado')) }}
 </span>
 </td>
 <td class="px-6 py-4 text-sm font-semibold">
 <a
 href="{{ route('fleet.maintenance.edit', $maintenance) }}"
 class="text-indigo-600 transition hover:text-indigo-400 "
 >
 Editar
 </a>
 <button
 wire:click="deleteMaintenance({{ $maintenance->id }})"
 wire:confirm="¿Está seguro de eliminar este registro?"
 class="ml-3 text-rose-600 transition hover:text-rose-500 "
 >
 Eliminar
 </button>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="6" class="px-6 py-6 text-center text-sm text-slate-500 ">
 No se encontraron registros de mantenimiento.
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>


 <div class="border-t border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 ">
 {{ $maintenances->links() }}
 </div>
 </div>
</div>
