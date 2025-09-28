<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Gestion de Pedidos</h1>
        <a href="{{ route('orders.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Nuevo Pedido</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-4 bg-white shadow rounded">
            <p class="text-sm text-gray-500">Total</p>
            <p class="text-2xl font-semibold">{{ $metrics['total'] }}</p>
        </div>
        <div class="p-4 bg-white shadow rounded">
            <p class="text-sm text-gray-500">Pendientes</p>
            <p class="text-2xl font-semibold">{{ $metrics['pending'] }}</p>
        </div>
        <div class="p-4 bg-white shadow rounded">
            <p class="text-sm text-gray-500">En ruta</p>
            <p class="text-2xl font-semibold">{{ $metrics['en_route'] }}</p>
        </div>
        <div class="p-4 bg-white shadow rounded">
            <p class="text-sm text-gray-500">Entregados</p>
            <p class="text-2xl font-semibold">{{ $metrics['delivered'] }}</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white shadow rounded">
        <div class="p-4 grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por referencia, origen, destino..." class="px-3 py-2 border rounded">
            <select wire:model.live="status" class="px-3 py-2 border rounded">
                <option value="">Todos los estados</option>
                <option value="pending">Pendiente</option>
                <option value="en_route">En ruta</option>
                <option value="delivered">Entregado</option>
                <option value="cancelled">Cancelado</option>
            </select>
            <select wire:model.live="client_id" class="px-3 py-2 border rounded">
                <option value="">Todos los clientes</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->business_name }}</option>
                @endforeach
            </select>
            <div class="text-sm text-gray-500 self-center">
                Resultados: {{ $orders->total() }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referencia</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fechas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asignacion</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($orders as $order)
                        @php
                            $statusStyles = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'en_route' => 'bg-blue-100 text-blue-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                            ];
                            $statusLabel = [
                                'pending' => 'Pendiente',
                                'en_route' => 'En ruta',
                                'delivered' => 'Entregado',
                                'cancelled' => 'Cancelado',
                            ][$order->status] ?? 'Pendiente';
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $order->reference }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->client->business_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div>{{ $order->origin }} -> {{ $order->destination }}</div>
                                <div class="text-xs text-gray-400">{{ \Illuminate\Support\Str::limit($order->cargo_details, 60) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>Recojo: {{ optional($order->pickup_date)->format('d/m/Y H:i') ?? 'Sin definir' }}</div>
                                <div>Entrega: {{ optional($order->delivery_date)->format('d/m/Y H:i') ?? 'Sin definir' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusStyles[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($order->activeAssignment)
                                    <div>{{ $order->activeAssignment->truck->plate_number }} / {{ $order->activeAssignment->driver->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $order->activeAssignment->status }}</div>
                                @else
                                    <span class="text-xs text-gray-400">Sin asignacion activa</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="{{ route('orders.edit', $order->id) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                <a href="{{ route('fleet.assignments.index', ['order' => $order->id]) }}" class="text-blue-600 hover:text-blue-900">Asignaciones</a>
                                <button wire:click="updateOrderStatus({{ $order->id }}, 'en_route')" class="text-yellow-600 hover:text-yellow-800">Marcar en ruta</button>
                                <button wire:click="updateOrderStatus({{ $order->id }}, 'delivered')" class="text-green-600 hover:text-green-800">Marcar entregado</button>
                                <button wire:click="deleteOrder({{ $order->id }})" wire:confirm="Esta seguro de eliminar el pedido?" class="text-red-600 hover:text-red-900">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No se encontraron pedidos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 bg-white border-t border-gray-200">
            {{ $orders->links() }}
        </div>
    </div>
</div>
