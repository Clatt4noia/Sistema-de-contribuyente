<table class="table table-md min-w-[1200px]">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <div class="space-y-1">
 <h1 class="text-2xl font-semibold text-token ">Gestión de Ordenes</h1>
 <p class="text-sm text-token ">Controla solicitudes de transporte, monitorea estados y sincroniza asignaciones.</p>
 </div>
    <a href="{{ route('orders.create') }}" class="btn btn-primary">
        Nueva Orden
    </a>
 </div>

 <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-token ">Total</p>
 <p class="mt-1 text-2xl font-semibold text-token ">{{ $metrics['total'] }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-token ">Pendientes</p>
 <p class="mt-1 text-2xl font-semibold text-warning ">{{ $metrics['pending'] }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-token ">En ruta</p>
 <p class="mt-1 text-2xl font-semibold text-accent ">{{ $metrics['en_route'] }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-token ">Entregados</p>
 <p class="mt-1 text-2xl font-semibold text-success ">{{ $metrics['delivered'] }}</p>
 </div>
 </div>

 @if (session()->has('message'))
 <div class="alert alert-success" role="alert">
 {{ session('message') }}
</div>
@endif

@if (session()->has('error'))
 <div class="alert alert-danger" role="alert">
 {{ session('error') }}
</div>
@endif

 <div class="surface-card shadow-lg">
 <div class="grid grid-cols-1 gap-4 border-b border-token px-4 py-4 md:grid-cols-4">
 <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por referencia, origen, destino..." class="form-control">
 <select wire:model.live="status" class="form-control">
 <option value="">Todos los estados</option>
 <option value="pending">Pendiente</option>
 <option value="en_route">En ruta</option>
 <option value="delivered">Entregado</option>
 <option value="cancelled">Cancelado</option>
 </select>
 <select wire:model.live="client_id" class="form-control">
 <option value="">Todos los clientes</option>
 @foreach($clients as $client)
 <option value="{{ $client->id }}">{{ $client->business_name }}</option>
 @endforeach
 </select>
 <div class="self-center text-sm text-token ">
 Resultados: {{ $orders->total() }}
 </div>
 </div>

    <div class="overflow-x-auto">
      <table class="table table-md">
        <thead>
          <tr class="table-row">
            <th class="table-header">Referencia</th>
            <th class="table-header">Cliente</th>
            <th class="table-header">Ruta</th>
            <th class="table-header">Fechas</th>
            <th class="table-header">Estado</th>
            <th class="table-header">Asignacion</th>
            <th class="table-header">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($orders as $order)
            @php
              $statusStyles = [
                'pending' => 'badge badge-warning',
                'en_route' => 'badge badge-accent',
                'delivered' => 'badge badge-success',
                'cancelled' => 'badge badge-danger',
              ];
              $statusLabel = [
                'pending' => 'Pendiente',
                'en_route' => 'En ruta',
                'delivered' => 'Entregado',
                'cancelled' => 'Cancelado',
              ][$order->status] ?? 'Pendiente';
            @endphp
            <tr class="table-row table-row-hover">
              <td class="table-cell whitespace-nowrap text-sm font-semibold text-token ">{{ $order->reference }}</td>
              <td class="table-cell whitespace-nowrap text-sm text-token ">{{ $order->client->business_name }}</td>
              <td class="table-cell text-sm text-token ">
                <div>{{ $order->origin }} → {{ $order->destination }}</div>
                <div class="text-xs text-token-muted ">{{ \Illuminate\Support\Str::limit($order->cargo_details, 60) }}</div>
              </td>
              <td class="table-cell whitespace-nowrap text-sm text-token ">
                <div>Recojo: {{ optional($order->pickup_date)->format('d/m/Y H:i') ?? 'Sin definir' }}</div>
                <div>Entrega: {{ optional($order->delivery_date)->format('d/m/Y H:i') ?? 'Sin definir' }}</div>
              </td>
              <td class="table-cell whitespace-nowrap">
                <span class="{{ $statusStyles[$order->status] ?? 'badge badge-accent' }}">
                  {{ $statusLabel }}
                </span>
              </td>
              <td class="table-cell text-sm text-token ">
                @if($order->activeAssignment)
                  <div>{{ $order->activeAssignment->truck->plate_number }} / {{ $order->activeAssignment->driver->name }}</div>
                  <div class="text-xs text-token-muted ">{{ $order->activeAssignment->status }}</div>
                @else
                  <span class="text-xs text-token-muted ">Sin asignacion activa</span>
                @endif
              </td>
              <td class="table-cell whitespace-nowrap text-sm font-medium">
                <div class="flex flex-wrap items-center gap-3">
                  <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-ghost btn-sm">Editar</a>
                  <a href="{{ route('fleet.assignments.index', ['order' => $order->id]) }}" class="btn btn-secondary btn-sm">Asignaciones</a>
                  <button wire:click="updateOrderStatus({{ $order->id }}, 'en_route')" class="btn btn-secondary btn-sm">Marcar en ruta</button>
                  <button wire:click="updateOrderStatus({{ $order->id }}, 'delivered')" class="btn btn-primary btn-sm">Marcar entregado</button>
                  <button wire:click="deleteOrder({{ $order->id }})" wire:confirm="Esta seguro de eliminar el Orden?" class="btn btn-danger btn-sm">Eliminar</button>
                </div>
              </td>
            </tr>
          @empty
            <tr class="table-row">
              <td colspan="7" class="table-empty">No se encontraron Ordenes</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="table-footer">
      {{ $orders->links() }}
    </div>
 </div>
</div>
