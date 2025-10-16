<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h1 class="text-2xl font-semibold text-token ">Pagos</h1>
    <a href="{{ route('billing.payments.create') }}" class="btn btn-primary">Registrar Pago</a>
 </div>

 @if (session()->has('message'))
 <div class="alert alert-success " role="alert">
 <p>{{ session('message') }}</p>
 </div>
 @endif

 <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-token ">{{ __('Pagos recibidos') }}</p>
 <p class="mt-1 text-xl font-semibold text-token ">{{ \App\Support\Formatters\MoneyFormatter::pen($totals['received']) }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-token ">Numero de pagos</p>
 <p class="mt-1 text-xl font-semibold text-token ">{{ $totals['count'] }}</p>
 </div>
 </div>

 <div class="surface-card overflow-hidden shadow-lg">
 <div class="grid grid-cols-1 gap-4 border-b border-token px-4 py-4 md:grid-cols-4">
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
        <table class="table table-md">
          <thead>
            <tr class="table-row">
              <th class="table-header">Factura</th>
              <th class="table-header">Cliente</th>
              <th class="table-header">Monto</th>
              <th class="table-header">Fecha</th>
              <th class="table-header">Metodo</th>
              <th class="table-header">Referencia</th>
              <th class="table-header">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($payments as $payment)
              <tr class="table-row table-row-hover">
                <td class="table-cell whitespace-nowrap text-sm font-medium text-token ">{{ $payment->invoice->invoice_number }}</td>
                <td class="table-cell whitespace-nowrap text-sm text-token ">{{ $payment->invoice->client->business_name }}</td>
                <td class="table-cell whitespace-nowrap text-sm font-semibold text-token ">{{ \App\Support\Formatters\MoneyFormatter::pen($payment->amount) }}</td>
                <td class="table-cell whitespace-nowrap text-sm text-token ">{{ $payment->paid_at->format('d/m/Y') }}</td>
                <td class="table-cell whitespace-nowrap text-sm text-token ">{{ $payment->method ?: '-' }}</td>
                <td class="table-cell whitespace-nowrap text-sm text-token ">{{ $payment->reference ?: '-' }}</td>
                <td class="table-cell whitespace-nowrap text-sm font-medium">
                  <a href="{{ route('billing.payments.edit', $payment->id) }}" class="btn btn-ghost btn-sm mr-2">Editar</a>
                  <button wire:click="deletePayment({{ $payment->id }})" wire:confirm="Eliminar este pago?" class="btn btn-danger btn-sm">Eliminar</button>
                </td>
              </tr>
            @empty
              <tr class="table-row">
                <td colspan="7" class="table-empty">No se encontraron pagos</td>
              </tr>
            @endforelse
          </tbody>
        </table>
    </div>


    <div class="table-footer">
      {{ $payments->links() }}
    </div>
 </div>
</div>
