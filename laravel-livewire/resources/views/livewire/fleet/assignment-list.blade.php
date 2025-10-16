<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h2 class="text-2xl font-semibold text-slate-900 ">Asignaciones de Vehiculos</h2>
    <a href="{{ route('fleet.assignments.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Nueva Asignacion
    </a>
 </div>

 @if (session()->has('message'))
 <div class="alert alert-success " role="alert">
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
      <table class="table table-md">
        <thead>
          <tr class="table-row">
            <th class="table-header">Pedido</th>
            <th class="table-header">Vehiculo</th>
            <th class="table-header">Conductor</th>
            <th class="table-header">Inicio</th>
            <th class="table-header">Fin</th>
            <th class="table-header">Estado</th>
            <th class="table-header">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($assignments as $assignment)
            @php
              $statusStyles = [
                'scheduled' => ['label' => 'Programada', 'class' => 'bg-warning-soft text-warning '],
                'in_progress' => ['label' => 'En ruta', 'class' => 'bg-accent-soft text-accent '],
                'completed' => ['label' => 'Completada', 'class' => 'bg-success-soft text-success-strong '],
                'cancelled' => ['label' => 'Cancelada', 'class' => 'bg-danger-soft text-danger-strong '],
              ];
              $statusConfig = $statusStyles[$assignment->status] ?? $statusStyles['scheduled'];
            @endphp
            <tr class="table-row table-row-hover">
              <td class="table-cell whitespace-nowrap">
                <div class="text-sm font-medium text-slate-900 ">{{ optional($assignment->order)->reference ?? 'Sin pedido' }}</div>
                <div class="text-sm text-slate-600 ">
                  @if($assignment->order)
                    {{ $assignment->order->origin }} -> {{ $assignment->order->destination }}
                  @else
                    {{ $assignment->description }}
                  @endif
                </div>
              </td>
              <td class="table-cell whitespace-nowrap">
                <div class="text-sm font-medium text-slate-900 ">{{ $assignment->truck->plate_number }}</div>
                <div class="text-sm text-slate-600 ">{{ $assignment->truck->brand }} {{ $assignment->truck->model }}</div>
              </td>
              <td class="table-cell whitespace-nowrap">
                <div class="text-sm font-medium text-slate-900 ">{{ $assignment->driver->name }} {{ $assignment->driver->last_name }}</div>
                <div class="text-sm text-slate-600 ">{{ $assignment->driver->document_number }}</div>
              </td>
              <td class="table-cell whitespace-nowrap text-sm text-slate-900 ">
                {{ $assignment->start_date?->format('d/m/Y H:i') }}
              </td>
              <td class="table-cell whitespace-nowrap text-sm text-slate-900 ">
                {{ $assignment->end_date ? $assignment->end_date->format('d/m/Y H:i') : 'En curso' }}
              </td>
              <td class="table-cell whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusConfig['class'] }}">
                  {{ $statusConfig['label'] }}
                </span>
              </td>
              <td class="table-cell whitespace-nowrap text-sm font-medium">
                <a href="{{ route('fleet.assignments.edit', $assignment->id) }}" class="btn btn-ghost btn-sm mr-2">
                  <i class="fas fa-edit"></i> Editar
                </a>
                <button wire:click="deleteAssignment({{ $assignment->id }})" wire:confirm="Esta seguro de eliminar esta asignacion?" class="btn btn-danger btn-sm">
                  <i class="fas fa-trash"></i> Eliminar
                </button>
              </td>
            </tr>
          @empty
            <tr class="table-row">
              <td colspan="7" class="table-empty">
                No se encontraron asignaciones
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="table-footer">
      {{ $assignments->links() }}
    </div>
  </div>
</div>

