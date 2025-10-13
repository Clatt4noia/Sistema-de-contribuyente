<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h1 class="text-2xl font-semibold text-slate-900 ">Facturas</h1>
 <a href="{{ route('billing.invoices.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 ">Nueva Factura</a>
 </div>

 @if (session()->has('message'))
 <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700 shadow-sm " role="alert">
 <p>{{ session('message') }}</p>
 </div>
 @endif

 @if (session()->has('error'))
 <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-600 shadow-sm " role="alert">
 <p>{{ session('error') }}</p>
 </div>
 @endif

 <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">{{ __('Emisión') }}</p>
 <p class="mt-1 text-xl font-semibold text-slate-900 ">{{ \App\Support\Formatters\MoneyFormatter::pen($totals['issued']) }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">{{ __('Pagado') }}</p>
 <p class="mt-1 text-xl font-semibold text-slate-900 ">{{ \App\Support\Formatters\MoneyFormatter::pen($totals['paid']) }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">{{ __('Vencido') }}</p>
 <p class="mt-1 text-xl font-semibold text-slate-900 ">{{ \App\Support\Formatters\MoneyFormatter::pen($totals['overdue']) }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">{{ __('Saldo') }}</p>
 <p class="mt-1 text-xl font-semibold text-slate-900 ">{{ \App\Support\Formatters\MoneyFormatter::pen($totals['balance']) }}</p>
 </div>
 </div>

 <div class="surface-card overflow-hidden shadow-lg">
 <div class="grid grid-cols-1 gap-4 border-b border-slate-200 px-4 py-4 md:grid-cols-4">
 <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por numero o cliente..." class="form-control">
 <select wire:model.live="status" class="form-control">
 <option value="">Todos los estados</option>
 <option value="draft">Borrador</option>
 <option value="issued">Emitida</option>
 <option value="paid">Pagada</option>
 <option value="overdue">Vencida</option>
 </select>
 <select wire:model.live="client_id" class="form-control">
 <option value="">Todos los clientes</option>
 @foreach($clients as $client)
 <option value="{{ $client->id }}">{{ $client->business_name }}</option>
 @endforeach
 </select>
 <select wire:model.live="order_id" class="form-control">
 <option value="">Todos los pedidos</option>
 @foreach($orders as $order)
 <option value="{{ $order->id }}">{{ $order->reference }}</option>
 @endforeach
 </select>
 </div>

 <div class="overflow-x-auto">
 <table class="surface-table">
 <thead>
 <tr>
 <th class="px-6 py-3">Factura</th>
 <th class="px-6 py-3">Cliente</th>
 <th class="px-6 py-3">Pedido</th>
 <th class="px-6 py-3">Emision / Venc.</th>
 <th class="px-6 py-3">Total</th>
 <th class="px-6 py-3">Saldo</th>
 <th class="px-6 py-3">Estado</th>
 <th class="px-6 py-3">Estado SUNAT</th>
 <th class="px-6 py-3">Documentos</th>
 <th class="px-6 py-3">Acciones</th>
 </tr>
 </thead>
 <tbody>
 @forelse($invoices as $invoice)
 @php
 $statusStyles = [
 'draft' => 'bg-slate-200 text-slate-700 ',
 'issued' => 'bg-sky-100 text-sky-700 ',
 'paid' => 'bg-emerald-100 text-emerald-700 ',
 'overdue' => 'bg-rose-100 text-rose-700 ',
 ];
 $statusLabel = [
 'draft' => 'Borrador',
 'issued' => 'Emitida',
 'paid' => 'Pagada',
 'overdue' => 'Vencida',
 ][$invoice->status] ?? 'Emitida';
 @endphp
 <tr class="transition hover:bg-slate-100 ">
 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 ">{{ $invoice->numero_completo ?: $invoice->invoice_number }}</td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 ">{{ $invoice->client->business_name }}</td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 ">{{ optional($invoice->order)->reference ?: '-' }}</td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 ">
 {{ $invoice->issue_date->format('d/m/Y') }}<br>
 {{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900 ">{{ \App\Support\Formatters\MoneyFormatter::pen($invoice->total) }}</td>
 <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900 ">{{ \App\Support\Formatters\MoneyFormatter::pen($invoice->balance) }}</td>
 <td class="px-6 py-4 whitespace-nowrap">
 <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusStyles[$invoice->status] ?? 'bg-gray-100 text-gray-800' }}">
 {{ $statusLabel }}
 </span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm">
 <livewire:billing.sunat-status-badge :status="$invoice->sunat_status" :message="$invoice->sunat_response_message" :key="'status-'.$invoice->id" />
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm">
 <livewire:billing.invoice-file-downloader :invoice="$invoice" :key="'files-'.$invoice->id" />
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-y-2">
 <div class="flex flex-wrap items-center gap-3">
 <a href="{{ route('billing.invoices.edit', $invoice->id) }}" class="font-semibold text-indigo-600 transition hover:text-indigo-700 ">Editar</a>
 <a href="{{ route('billing.payments.create', ['invoice' => $invoice->id]) }}" class="font-semibold text-cyan-600 transition hover:text-cyan-700 ">Registrar pago</a>
 </div>
 <div class="flex flex-wrap items-center gap-3">
 <a href="{{ route('billing.invoices.electronic', $invoice->id) }}" class="font-semibold text-emerald-600 transition hover:text-emerald-700 ">Emitir SUNAT</a>
 @if($invoice->balance <= 0 && $invoice->status !== 'paid')
 <button wire:click="markAsPaid({{ $invoice->id }})" class="font-semibold text-slate-700 transition hover:text-slate-900 ">Marcar pagada</button>
 @endif
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="8" class="px-6 py-4 text-center text-slate-500 ">No se encontraron facturas</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>


 <div class="border-t border-slate-200 px-4 py-3 ">
 {{ $invoices->links() }}
 </div>
 </div>
</div>
