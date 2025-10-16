<section class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Facturas recientes') }}</h2>
 </header>

  <div class="overflow-x-auto">
    <table class="table table-md">
      <thead>
        <tr class="table-row">
          <th class="table-header">{{ __('Número') }}</th>
          <th class="table-header">{{ __('Cliente') }}</th>
          <th class="table-header">{{ __('Estado') }}</th>
          <th class="table-header">{{ __('Monto') }}</th>
          <th class="table-header">{{ __('Emisión') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($this->invoices as $invoice)
          <tr class="table-row table-row-hover">
            <td class="table-cell text-sm font-medium text-slate-900 ">{{ $invoice->invoice_number ?? '—' }}</td>
            <td class="table-cell text-sm text-slate-600 ">
              {{ optional($invoice->client)->business_name ?? optional($invoice->client)->contact_name ?? '—' }}
            </td>
            <td class="table-cell">
              <span class="rounded-full bg-success-soft px-3 py-1 text-xs font-semibold text-success-strong ">

                {{ $invoice->status ? __($invoice->status) : __('pendiente') }}
              </span>
            </td>
            <td class="table-cell text-sm text-slate-600 ">{{ $invoice->formatted_total }}</td>
            <td class="table-cell text-sm text-slate-600 ">{{ optional($invoice->issue_date)?->format('d/m/Y') ?? '—' }}</td>
          </tr>
        @empty
          <tr class="table-row">
            <td colspan="5" class="table-empty">{{ __('Carga facturas para monitorear el flujo de ingresos.') }}</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</section>
