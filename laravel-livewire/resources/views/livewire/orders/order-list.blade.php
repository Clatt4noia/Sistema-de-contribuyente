<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <div class="space-y-1">
 <h1 class="text-2xl font-semibold text-slate-900 ">Gestion de Pedidos</h1>
 <p class="text-sm text-slate-500 ">Controla solicitudes de transporte, monitorea estados y sincroniza asignaciones.</p>
 </div>
    <a href="{{ route('orders.create') }}" class="btn btn-primary">
        Nuevo Pedido
    </a>
 </div>

 <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">Total</p>
 <p class="mt-1 text-2xl font-semibold text-slate-900 ">{{ $metrics['total'] }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">Pendientes</p>
 <p class="mt-1 text-2xl font-semibold text-amber-600 ">{{ $metrics['pending'] }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">En ruta</p>
 <p class="mt-1 text-2xl font-semibold text-sky-600 ">{{ $metrics['en_route'] }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">Entregados</p>
 <p class="mt-1 text-2xl font-semibold text-emerald-600 ">{{ $metrics['delivered'] }}</p>
 </div>
 </div>

 @if (session()->has('message'))
 <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm " role="alert">
 {{ session('message') }}
 </div>
 @endif

 @if (session()->has('error'))
 <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 shadow-sm " role="alert">
 {{ session('error') }}
 </div>
 @endif

 <div class="surface-card shadow-lg">
 <div class="grid grid-cols-1 gap-4 border-b border-slate-200 px-4 py-4 md:grid-cols-4">
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
 <div class="self-center text-sm text-slate-500 ">
 Resultados: {{ $orders->total() }}
 </div>
 </div>

 <div class="overflow-x-auto">
 <table class="surface-table">
 <thead>
 <tr>
 <th class="px-6 py-3">Referencia</th>
 <th class="px-6 py-3">Cliente</th>
 <th class="px-6 py-3">Ruta</th>
 <th class="px-6 py-3">Fechas</th>
 <th class="px-6 py-3">Estado</th>
 <th class="px-6 py-3">Asignacion</th>
 <th class="px-6 py-3">Acciones</th>
 </tr>
 </thead>
 <tbody>
 @forelse($orders as $order)
 @php
 $statusStyles = [
 'pending' => 'bg-amber-100 text-amber-700 ',
 'en_route' => 'bg-sky-100 text-sky-700 ',
 'delivered' => 'bg-emerald-100 text-emerald-700 ',
 'cancelled' => 'bg-rose-100 text-rose-700 ',
 ];
 $statusLabel = [
 'pending' => 'Pendiente',
 'en_route' => 'En ruta',
 'delivered' => 'Entregado',
 'cancelled' => 'Cancelado',
 ][$order->status] ?? 'Pendiente';
 @endphp
 <tr class="transition hover:bg-slate-100 ">
 <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900 ">{{ $order->reference }}</td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 ">{{ $order->client->business_name }}</td>
 <td class="px-6 py-4 text-sm text-slate-600 ">
 <div>{{ $order->origin }} → {{ $order->destination }}</div>
 <div class="text-xs text-slate-400 ">{{ \Illuminate\Support\Str::limit($order->cargo_details, 60) }}</div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 ">
 <div>Recojo: {{ optional($order->pickup_date)->format('d/m/Y H:i') ?? 'Sin definir' }}</div>
 <div>Entrega: {{ optional($order->delivery_date)->format('d/m/Y H:i') ?? 'Sin definir' }}</div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusStyles[$order->status] ?? 'bg-slate-100 text-slate-700 ' }}">
 {{ $statusLabel }}
 </span>
 </td>
 <td class="px-6 py-4 text-sm text-slate-600 ">
 @if($order->activeAssignment)
 <div>{{ $order->activeAssignment->truck->plate_number }} / {{ $order->activeAssignment->driver->name }}</div>
 <div class="text-xs text-slate-400 ">{{ $order->activeAssignment->status }}</div>
 @else
 <span class="text-xs text-slate-400 ">Sin asignacion activa</span>
 @endif
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
 <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-ghost btn-sm">Editar</a>
        <a href="{{ route('fleet.assignments.index', ['order' => $order->id]) }}" class="btn btn-secondary btn-sm">Asignaciones</a>
        <button wire:click="updateOrderStatus({{ $order->id }}, 'en_route')" class="btn btn-secondary btn-sm">Marcar en ruta</button>
        <button wire:click="updateOrderStatus({{ $order->id }}, 'delivered')" class="btn btn-primary btn-sm">Marcar entregado</button>
        <button wire:click="deleteOrder({{ $order->id }})" wire:confirm="Esta seguro de eliminar el pedido?" class="btn btn-danger btn-sm">Eliminar</button>
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="7" class="px-6 py-4 text-center text-sm text-slate-500 ">No se encontraron pedidos</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 <div class="border-t border-slate-200 bg-slate-50 px-4 py-3 ">
 {{ $orders->links() }}
 </div>
 </div>
</div>
