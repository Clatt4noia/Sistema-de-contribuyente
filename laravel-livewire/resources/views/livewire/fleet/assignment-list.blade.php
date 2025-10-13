<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h2 class="text-2xl font-semibold text-slate-900 ">Asignaciones de Vehiculos</h2>
 <a href="{{ route('fleet.assignments.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 ">
 <i class="fas fa-plus"></i>
 Nueva Asignacion
 </a>
 </div>

 @if (session()->has('message'))
 <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700 shadow-sm " role="alert">
 <p>{{ session('message') }}</p>
 </div>
 @endif

 <div class="surface-card overflow-hidden shadow-lg">
 <div class="grid grid-cols-1 gap-4 border-b border-slate-200 px-4 py-4 md:grid-cols-4">
 <div class="md:col-span-2">
 <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por descripcion, pedido, vehiculo o chofer..." class="form-control">
 </div>
 <div>
 <select wire:model.live="status" class="form-control">
 <option value="">Todos los estados</option>
 <option value="scheduled">Programada</option>
 <option value="in_progress">En ruta</option>
 <option value="completed">Completada</option>
 <option value="cancelled">Cancelada</option>
 </select>
 </div>
 <div>
 <select wire:model.live="order_id" class="form-control">
 <option value="">Todos los pedidos</option>
 @foreach($orders as $order)
 <option value="{{ $order->id }}">{{ $order->reference }} - {{ $order->origin }} -> {{ $order->destination }}</option>
 @endforeach
 </select>
 </div>
 <div>
 <select wire:model.live="truck_id" class="form-control">
 <option value="">Todos los vehiculos</option>
 @foreach($trucks as $truck)
 <option value="{{ $truck->id }}">{{ $truck->plate_number }} - {{ $truck->brand }} {{ $truck->model }}</option>
 @endforeach
 </select>
 </div>
 <div>
 <select wire:model.live="driver_id" class="form-control">
 <option value="">Todos los conductores</option>
 @foreach($drivers as $driver)
 <option value="{{ $driver->id }}">{{ $driver->name }} {{ $driver->last_name }}</option>
 @endforeach
 </select>
 </div>
 </div>
 </div>

 <div class="surface-card overflow-hidden shadow-lg">
 <div class="overflow-x-auto">
 <table class="surface-table">
 <thead>
 <tr>
 <th class="px-6 py-3">Pedido</th>
 <th class="px-6 py-3">Vehiculo</th>
 <th class="px-6 py-3">Conductor</th>
 <th class="px-6 py-3">Inicio</th>
 <th class="px-6 py-3">Fin</th>
 <th class="px-6 py-3">Estado</th>
 <th class="px-6 py-3">Acciones</th>
 </tr>
 </thead>
 <tbody>
 @forelse($assignments as $assignment)
 @php
 $statusStyles = [
 'scheduled' => ['label' => 'Programada', 'class' => 'bg-amber-100 text-amber-700 '],
 'in_progress' => ['label' => 'En ruta', 'class' => 'bg-sky-100 text-sky-700 '],
 'completed' => ['label' => 'Completada', 'class' => 'bg-emerald-100 text-emerald-700 '],
 'cancelled' => ['label' => 'Cancelada', 'class' => 'bg-rose-100 text-rose-700 '],
 ];
 $statusConfig = $statusStyles[$assignment->status] ?? $statusStyles['scheduled'];
 @endphp
 <tr class="transition hover:bg-slate-100 ">
 <td class="px-6 py-4 whitespace-nowrap">
 <div class="text-sm font-medium text-slate-900 ">{{ optional($assignment->order)->reference ?? 'Sin pedido' }}</div>
 <div class="text-sm text-slate-600 ">
 @if($assignment->order)
 {{ $assignment->order->origin }} -> {{ $assignment->order->destination }}
 @else
 {{ $assignment->description }}
 @endif
 </div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <div class="text-sm font-medium text-slate-900 ">{{ $assignment->truck->plate_number }}</div>
 <div class="text-sm text-slate-600 ">{{ $assignment->truck->brand }} {{ $assignment->truck->model }}</div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <div class="text-sm font-medium text-slate-900 ">{{ $assignment->driver->name }} {{ $assignment->driver->last_name }}</div>
 <div class="text-sm text-slate-600 ">{{ $assignment->driver->document_number }}</div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 ">
 {{ $assignment->start_date?->format('d/m/Y H:i') }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 ">
 {{ $assignment->end_date ? $assignment->end_date->format('d/m/Y H:i') : 'En curso' }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusConfig['class'] }}">
 {{ $statusConfig['label'] }}
 </span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
 <a href="{{ route('fleet.assignments.edit', $assignment->id) }}" class="mr-3 font-semibold text-indigo-600 transition hover:text-indigo-700 ">
 <i class="fas fa-edit"></i> Editar
 </a>
 <button wire:click="deleteAssignment({{ $assignment->id }})" wire:confirm="Esta seguro de eliminar esta asignacion?" class="font-semibold text-rose-600 transition hover:text-rose-700 ">
 <i class="fas fa-trash"></i> Eliminar
 </button>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="7" class="px-6 py-4 text-center text-slate-500 ">
 No se encontraron asignaciones
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 <div class="border-t border-slate-200 px-4 py-3 ">
 {{ $assignments->links() }}
 </div>
 </div>
</div>

