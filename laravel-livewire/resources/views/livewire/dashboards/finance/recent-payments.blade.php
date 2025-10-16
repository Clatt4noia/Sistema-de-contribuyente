<section class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Pagos recientes') }}</h2>
 </header>

  <div class="overflow-x-auto">
    <table class="table table-md">
      <thead>
        <tr class="table-row">
          <th class="table-header">{{ __('Referencia') }}</th>
          <th class="table-header">{{ __('Cliente') }}</th>
          <th class="table-header">{{ __('Monto') }}</th>
          <th class="table-header">{{ __('Fecha') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($this->payments as $payment)
          <tr class="table-row table-row-hover">
            <td class="table-cell text-sm font-medium text-slate-900 ">{{ $payment->reference ?? '—' }}</td>
            <td class="table-cell text-sm text-slate-600 ">
              {{ optional($payment->invoice?->client)->business_name ?? optional($payment->invoice?->client)->contact_name ?? '—' }}
            </td>
            <td class="table-cell text-sm text-slate-600 ">{{ $payment->formatted_amount }}</td>
            <td class="table-cell text-sm text-slate-600 ">{{ optional($payment->paid_at)?->format('d/m/Y') ?? '—' }}</td>
          </tr>
        @empty
          <tr class="table-row">
            <td colspan="4" class="table-empty">{{ __('Registra pagos para analizar liquidez.') }}</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</section>
