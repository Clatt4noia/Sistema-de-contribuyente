<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h1 class="text-2xl font-semibold text-token ">Facturas</h1>
    <a href="{{ route('billing.invoices.create') }}" class="btn btn-primary">Nueva Factura</a>
 </div>

 @if (session()->has('message'))
 <div class="alert alert-success " role="alert">
 <p>{{ session('message') }}</p>
 </div>
 @endif

 @if (session()->has('error'))
 <div class="alert alert-danger " role="alert">
 <p>{{ session('error') }}</p>
 </div>
 @endif

 <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-token ">{{ __('Emisión') }}</p>
 <p class="mt-1 text-xl font-semibold text-token ">{{ \App\Support\Formatters\MoneyFormatter::pen($totals['issued']) }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-token ">{{ __('Pagado') }}</p>
 <p class="mt-1 text-xl font-semibold text-token ">{{ \App\Support\Formatters\MoneyFormatter::pen($totals['paid']) }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-token ">{{ __('Vencido') }}</p>
 <p class="mt-1 text-xl font-semibold text-token ">{{ \App\Support\Formatters\MoneyFormatter::pen($totals['overdue']) }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-token ">{{ __('Saldo') }}</p>
 <p class="mt-1 text-xl font-semibold text-token ">{{ \App\Support\Formatters\MoneyFormatter::pen($totals['balance']) }}</p>
 </div>
 </div>

 <div class="surface-card overflow-hidden shadow-lg">
 <div class="grid grid-cols-1 gap-4 border-b border-token px-4 py-4 md:grid-cols-4">
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
 <option value="">Todos los Ordenes</option>
 @foreach($orders as $order)
 <option value="{{ $order->id }}">{{ $order->reference }}</option>
 @endforeach
 </select>
 </div>

 <div class="overflow-x-auto">
        <table class="table table-md">
          <thead>
            <tr class="table-row">
              <th class="table-header">Factura</th>
              <th class="table-header">Cliente</th>
              <th class="table-header">Orden</th>
              <th class="table-header">GRE vinculada</th>
              <th class="table-header">Emision / Venc.</th>
              <th class="table-header">Total</th>
              <th class="table-header">Saldo</th>
              <th class="table-header">Estado</th>
              <th class="table-header">Estado SUNAT</th>
              <th class="table-header">Documentos</th>
              <th class="table-header">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($invoices as $invoice)
              @php
                $statusStyles = [
                  'draft' => 'bg-surface-strong text-token ',
                  'issued' => 'bg-accent-soft text-accent ',
                  'paid' => 'bg-success-soft text-success-strong ',
                  'overdue' => 'bg-danger-soft text-danger-strong ',
                ];
                $statusLabel = [
                  'draft' => 'Borrador',
                  'issued' => 'Emitida',
                  'paid' => 'Pagada',
                  'overdue' => 'Vencida',
                ][$invoice->status] ?? 'Emitida';
              @endphp
              <tr class="table-row table-row-hover">
                <td class="table-cell whitespace-nowrap text-sm font-medium text-token ">{{ $invoice->numero_completo ?: $invoice->invoice_number }}</td>
                <td class="table-cell whitespace-nowrap text-sm text-token ">{{ $invoice->client->business_name }}</td>
                <td class="table-cell whitespace-nowrap text-sm text-token ">{{ optional($invoice->order)->reference ?: '-' }}</td>
                <td class="table-cell whitespace-nowrap text-sm text-token ">
                  @if($invoice->transportGuide)
                    <div class="flex flex-col gap-1">
                      <a
                        href="{{ route('billing.transport-guides.show', $invoice->transportGuide) }}"
                        class="link"
                      >
                        {{ $invoice->transportGuide->display_code }}
                      </a>
                      @php
                        $greStatus = match ($invoice->transportGuide->sunat_status) {
                          'accepted' => 'aceptado',
                          'rejected', 'error', 'cancelled' => 'rechazado',
                          'pending', 'sent' => 'pendiente',
                          default => 'observado',
                        };
                      @endphp
                      <livewire:billing.sunat-status-badge :status="$greStatus" :message="$invoice->transportGuide->sunat_notes" :key="'gre-status-'.$invoice->id" />
                    </div>
                  @else
                    <span class="text-token-muted">Sin GRE</span>
                  @endif
                </td>
                <td class="table-cell whitespace-nowrap text-sm text-token ">
                  {{ $invoice->issue_date->format('d/m/Y') }}<br>
                  {{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}
                </td>
                <td class="table-cell whitespace-nowrap text-sm font-semibold text-token ">{{ \App\Support\Formatters\MoneyFormatter::pen($invoice->total) }}</td>
                <td class="table-cell whitespace-nowrap text-sm font-semibold text-token ">{{ \App\Support\Formatters\MoneyFormatter::pen($invoice->balance) }}</td>
                <td class="table-cell whitespace-nowrap">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusStyles[$invoice->status] ?? 'bg-accent-soft text-accent' }}">
                    {{ $statusLabel }}
                  </span>
                </td>
                <td class="table-cell whitespace-nowrap text-sm">
                  <livewire:billing.sunat-status-badge :status="$invoice->sunat_status" :message="$invoice->sunat_response_message" :key="'status-'.$invoice->id" />
                </td>
                <td class="table-cell whitespace-nowrap text-sm">
                  <livewire:billing.invoice-file-downloader :invoice="$invoice" :key="'files-'.$invoice->id" />
                </td>
                <td class="table-cell whitespace-nowrap text-sm font-medium space-y-2">
                  <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('billing.invoices.edit', $invoice->id) }}" class="btn btn-ghost btn-sm">Editar</a>
                    <a href="{{ route('billing.payments.create', ['invoice' => $invoice->id]) }}" class="btn btn-primary btn-sm">Registrar pago</a>
                  </div>
                  <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('billing.invoices.electronic', $invoice->id) }}" class="btn btn-secondary btn-sm">Emitir SUNAT</a>
                    @if($invoice->balance <= 0 && $invoice->status !== 'paid')
                      <button wire:click="markAsPaid({{ $invoice->id }})" class="btn btn-primary btn-sm">Marcar pagada</button>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr class="table-row">
                <td colspan="11" class="table-empty">No se encontraron facturas</td>
              </tr>
            @endforelse
          </tbody>
        </table>
    </div>


    <div class="table-footer">
      {{ $invoices->links() }}
    </div>
 </div>
</div>
