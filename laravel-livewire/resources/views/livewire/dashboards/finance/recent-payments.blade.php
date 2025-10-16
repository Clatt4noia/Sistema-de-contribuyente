<section class="surface-card">
 <header class="border-b border-token px-6 py-5 ">
 <h2 class="text-lg font-semibold text-token ">{{ __('Pagos recientes') }}</h2>
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
            <td class="table-cell text-sm font-medium text-token ">{{ $payment->reference ?? '—' }}</td>
            <td class="table-cell text-sm text-token ">
              {{ optional($payment->invoice?->client)->business_name ?? optional($payment->invoice?->client)->contact_name ?? '—' }}
            </td>
            <td class="table-cell text-sm text-token ">{{ $payment->formatted_amount }}</td>
            <td class="table-cell text-sm text-token ">{{ optional($payment->paid_at)?->format('d/m/Y') ?? '—' }}</td>
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
