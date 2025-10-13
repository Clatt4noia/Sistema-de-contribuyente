<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h1 class="text-2xl font-semibold text-slate-900 ">Pagos</h1>
 <a href="{{ route('billing.payments.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 ">Registrar Pago</a>
 </div>

 @if (session()->has('message'))
 <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700 shadow-sm " role="alert">
 <p>{{ session('message') }}</p>
 </div>
 @endif

 <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">{{ __('Pagos recibidos') }}</p>
 <p class="mt-1 text-xl font-semibold text-slate-900 ">{{ \App\Support\Formatters\MoneyFormatter::pen($totals['received']) }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">Numero de pagos</p>
 <p class="mt-1 text-xl font-semibold text-slate-900 ">{{ $totals['count'] }}</p>
 </div>
 </div>


 <div class="surface-card overflow-hidden shadow-lg">
 <div class="grid grid-cols-1 gap-4 border-b border-slate-200 px-4 py-4 md:grid-cols-4">
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
 <tr class="transition hover:bg-slate-100 ">
 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 ">{{ $payment->invoice->invoice_number }}</td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 ">{{ $payment->invoice->client->business_name }}</td>
 <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900 ">{{ \App\Support\Formatters\MoneyFormatter::pen($payment->amount) }}</td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 ">{{ $payment->paid_at->format('d/m/Y') }}</td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 ">{{ $payment->method ?: '-' }}</td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 ">{{ $payment->reference ?: '-' }}</td>
 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
 <a href="{{ route('billing.payments.edit', $payment->id) }}" class="mr-3 font-semibold text-indigo-600 transition hover:text-indigo-700 ">Editar</a>
 <button wire:click="deletePayment({{ $payment->id }})" wire:confirm="Eliminar este pago?" class="font-semibold text-rose-600 transition hover:text-rose-700 ">Eliminar</button>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="7" class="px-6 py-4 text-center text-slate-500 ">No se encontraron pagos</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>


 <div class="border-t border-slate-200 px-4 py-3 ">
 {{ $payments->links() }}
 </div>
 </div>
</div>
