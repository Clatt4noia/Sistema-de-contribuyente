<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Pagos</h1>
        <a href="{{ route('billing.payments.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Registrar Pago</a>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-4 bg-white shadow rounded">
            <p class="text-sm text-gray-500">Pagos recibidos</p>
            <p class="text-xl font-semibold">S/ {{ number_format($totals['received'], 2) }}</p>
        </div>
        <div class="p-4 bg-white shadow rounded">
            <p class="text-sm text-gray-500">Numero de pagos</p>
            <p class="text-xl font-semibold">{{ $totals['count'] }}</p>
        </div>
    </div>

    <div class="bg-white shadow rounded">
        <div class="p-4 grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por metodo o referencia..." class="px-3 py-2 border rounded">
            <select wire:model.live="invoice_id" class="px-3 py-2 border rounded">
                <option value="">Todas las facturas</option>
                @foreach($invoices as $invoice)
                    <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }}</option>
                @endforeach
            </select>
            <input type="text" wire:model.live="method" placeholder="Filtrar por metodo" class="px-3 py-2 border rounded">
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Factura</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metodo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referencia</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($payments as $payment)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $payment->invoice->invoice_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->invoice->client->business_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">S/ {{ number_format($payment->amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->paid_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->method ?: '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->reference ?: '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('billing.payments.edit', $payment->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</a>
                                <button wire:click="deletePayment({{ $payment->id }})" wire:confirm="Eliminar este pago?" class="text-red-600 hover:text-red-900">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No se encontraron pagos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 bg-white border-t border-gray-200">
            {{ $payments->links() }}
        </div>
    </div>
</div>
