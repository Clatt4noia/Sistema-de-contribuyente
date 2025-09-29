<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Pagos</h1>
        <a href="{{ route('billing.payments.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:bg-indigo-400 dark:text-slate-900 dark:hover:bg-indigo-300">Registrar Pago</a>
    </div>

    @if (session()->has('message'))
        <div class="rounded-2xl border border-emerald-200/70 bg-emerald-50/80 p-4 text-sm font-medium text-emerald-700 shadow-sm dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-slate-500 dark:text-slate-300">Pagos recibidos</p>
            <p class="mt-1 text-xl font-semibold text-slate-900 dark:text-slate-100">S/ {{ number_format($totals['received'], 2) }}</p>
        </div>
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-slate-500 dark:text-slate-300">Numero de pagos</p>
            <p class="mt-1 text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $totals['count'] }}</p>
        </div>
    </div>

    <div class="surface-card overflow-hidden shadow-lg">
        <div class="grid grid-cols-1 gap-4 border-b border-slate-200/70 px-4 py-4 dark:border-slate-800/70 md:grid-cols-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por metodo o referencia..." class="form-control">
            <select wire:model.live="invoice_id" class="form-control">
                <option value="">Todas las facturas</option>
                @foreach($invoices as $invoice)
                    <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }}</option>
                @endforeach
            </select>
            <input type="text" wire:model.live="method" placeholder="Filtrar por metodo" class="form-control">
        </div>

        <div class="overflow-x-auto">
            <table class="surface-table">
                <thead>
                    <tr>
                        <th class="px-6 py-3">Factura</th>
                        <th class="px-6 py-3">Cliente</th>
                        <th class="px-6 py-3">Monto</th>
                        <th class="px-6 py-3">Fecha</th>
                        <th class="px-6 py-3">Metodo</th>
                        <th class="px-6 py-3">Referencia</th>
                        <th class="px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr class="transition hover:bg-slate-900/5 dark:hover:bg-white/10">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-slate-100">{{ $payment->invoice->invoice_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">{{ $payment->invoice->client->business_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-slate-100">S/ {{ number_format($payment->amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">{{ $payment->paid_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">{{ $payment->method ?: '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">{{ $payment->reference ?: '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('billing.payments.edit', $payment->id) }}" class="mr-3 font-semibold text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-300 dark:hover:text-indigo-200">Editar</a>
                                <button wire:click="deletePayment({{ $payment->id }})" wire:confirm="Eliminar este pago?" class="font-semibold text-rose-600 transition hover:text-rose-700 dark:text-rose-300 dark:hover:text-rose-200">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-slate-500 dark:text-slate-400">No se encontraron pagos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200/70 px-4 py-3 dark:border-slate-800/70">
            {{ $payments->links() }}
        </div>
    </div>
</div>
