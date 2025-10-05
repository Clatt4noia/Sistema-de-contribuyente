<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Gestion de Pedidos</h1>
            <p class="text-sm text-slate-500 dark:text-slate-300">Controla solicitudes de transporte, monitorea estados y sincroniza asignaciones.</p>
        </div>
        <a href="{{ route('orders.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:bg-indigo-400 dark:text-slate-900 dark:hover:bg-indigo-300">
            Nuevo Pedido
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-slate-500 dark:text-slate-300">Total</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $metrics['total'] }}</p>
        </div>
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-slate-500 dark:text-slate-300">Pendientes</p>
            <p class="mt-1 text-2xl font-semibold text-amber-600 dark:text-amber-300">{{ $metrics['pending'] }}</p>
        </div>
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-slate-500 dark:text-slate-300">En ruta</p>
            <p class="mt-1 text-2xl font-semibold text-sky-600 dark:text-sky-300">{{ $metrics['en_route'] }}</p>
        </div>
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-slate-500 dark:text-slate-300">Entregados</p>
            <p class="mt-1 text-2xl font-semibold text-emerald-600 dark:text-emerald-300">{{ $metrics['delivered'] }}</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="rounded-2xl border border-emerald-200/70 bg-emerald-50/80 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200" role="alert">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-2xl border border-rose-200/70 bg-rose-50/80 px-4 py-3 text-sm font-medium text-rose-700 shadow-sm dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="surface-card shadow-lg">
        <div class="grid grid-cols-1 gap-4 border-b border-slate-200/70 px-4 py-4 dark:border-slate-800/70 md:grid-cols-4">
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
            <div class="self-center text-sm text-slate-500 dark:text-slate-300">
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
                                'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200',
                                'en_route' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-200',
                                'delivered' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200',
                                'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-200',
                            ];
                            $statusLabel = [
                                'pending' => 'Pendiente',
                                'en_route' => 'En ruta',
                                'delivered' => 'Entregado',
                                'cancelled' => 'Cancelado',
                            ][$order->status] ?? 'Pendiente';
                        @endphp
                        <tr class="transition hover:bg-slate-900/5 dark:hover:bg-white/10">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $order->reference }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">{{ $order->client->business_name }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                <div>{{ $order->origin }} → {{ $order->destination }}</div>
                                <div class="text-xs text-slate-400 dark:text-slate-500">{{ \Illuminate\Support\Str::limit($order->cargo_details, 60) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                <div>Recojo: {{ optional($order->pickup_date)->format('d/m/Y H:i') ?? 'Sin definir' }}</div>
                                <div>Entrega: {{ optional($order->delivery_date)->format('d/m/Y H:i') ?? 'Sin definir' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusStyles[$order->status] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200' }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                @if($order->activeAssignment)
                                    <div>{{ $order->activeAssignment->truck->plate_number }} / {{ $order->activeAssignment->driver->name }}</div>
                                    <div class="text-xs text-slate-400 dark:text-slate-500">{{ $order->activeAssignment->status }}</div>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-500">Sin asignacion activa</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex flex-wrap items-center gap-3">
                                    <a href="{{ route('orders.edit', $order->id) }}" class="font-semibold text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-300 dark:hover:text-indigo-200">Editar</a>
                                    <a href="{{ route('fleet.assignments.index', ['order' => $order->id]) }}" class="font-semibold text-sky-600 transition hover:text-sky-700 dark:text-sky-300 dark:hover:text-sky-200">Asignaciones</a>
                                    <button wire:click="updateOrderStatus({{ $order->id }}, 'en_route')" class="font-semibold text-amber-600 transition hover:text-amber-700 dark:text-amber-300 dark:hover:text-amber-200">Marcar en ruta</button>
                                    <button wire:click="updateOrderStatus({{ $order->id }}, 'delivered')" class="font-semibold text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-300 dark:hover:text-emerald-200">Marcar entregado</button>
                                    <button wire:click="deleteOrder({{ $order->id }})" wire:confirm="Esta seguro de eliminar el pedido?" class="font-semibold text-rose-600 transition hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-slate-500 dark:text-slate-400">No se encontraron pedidos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200/70 bg-slate-50/60 px-4 py-3 dark:border-slate-800/70 dark:bg-slate-900/40">
            {{ $orders->links() }}
        </div>
    </div>
</div>
