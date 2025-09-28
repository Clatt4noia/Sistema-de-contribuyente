<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Facturas</h1>
        <a href="{{ route('billing.invoices.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Nueva Factura</a>
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

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-4 bg-white shadow rounded">
            <p class="text-sm text-gray-500">Emision</p>
            <p class="text-xl font-semibold">S/ {{ number_format($totals['issued'], 2) }}</p>
        </div>
        <div class="p-4 bg-white shadow rounded">
            <p class="text-sm text-gray-500">Pagado</p>
            <p class="text-xl font-semibold">S/ {{ number_format($totals['paid'], 2) }}</p>
        </div>
        <div class="p-4 bg-white shadow rounded">
            <p class="text-sm text-gray-500">Vencido</p>
            <p class="text-xl font-semibold">S/ {{ number_format($totals['overdue'], 2) }}</p>
        </div>
        <div class="p-4 bg-white shadow rounded">
            <p class="text-sm text-gray-500">Saldo</p>
            <p class="text-xl font-semibold">S/ {{ number_format($totals['balance'], 2) }}</p>
        </div>
    </div>

    <div class="bg-white shadow rounded">
        <div class="p-4 grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por numero o cliente..." class="px-3 py-2 border rounded">
            <select wire:model.live="status" class="px-3 py-2 border rounded">
                <option value="">Todos los estados</option>
                <option value="draft">Borrador</option>
                <option value="issued">Emitida</option>
                <option value="paid">Pagada</option>
                <option value="overdue">Vencida</option>
            </select>
            <select wire:model.live="client_id" class="px-3 py-2 border rounded">
                <option value="">Todos los clientes</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->business_name }}</option>
                @endforeach
            </select>
            <select wire:model.live="order_id" class="px-3 py-2 border rounded">
                <option value="">Todos los pedidos</option>
                @foreach($orders as $order)
                    <option value="{{ $order->id }}">{{ $order->reference }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Factura</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pedido</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emision / Venc.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($invoices as $invoice)
                        @php
                            $statusStyles = [
                                'draft' => 'bg-gray-100 text-gray-800',
                                'issued' => 'bg-blue-100 text-blue-800',
                                'paid' => 'bg-green-100 text-green-800',
                                'overdue' => 'bg-red-100 text-red-800',
                            ];
                            $statusLabel = [
                                'draft' => 'Borrador',
                                'issued' => 'Emitida',
                                'paid' => 'Pagada',
                                'overdue' => 'Vencida',
                            ][$invoice->status] ?? 'Emitida';
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->client->business_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($invoice->order)->reference ?: '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $invoice->issue_date->format('d/m/Y') }}<br>
                                {{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">S/ {{ number_format($invoice->total, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">S/ {{ number_format($invoice->balance, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusStyles[$invoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                <a href="{{ route('billing.invoices.edit', $invoice->id) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                <a href="{{ route('billing.payments.create', ['invoice' => $invoice->id]) }}" class="text-blue-600 hover:text-blue-900">Registrar pago</a>
                                @if($invoice->balance <= 0 && $invoice->status !== 'paid')
                                    <button wire:click="markAsPaid({{ $invoice->id }})" class="text-green-600 hover:text-green-800">Marcar pagada</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">No se encontraron facturas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 bg-white border-t border-gray-200">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
