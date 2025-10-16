<div class="space-y-6">
 <section class="grid gap-6 lg:grid-cols-[2fr_1fr]">
 <article class="surface-card">
 <header class="flex items-center justify-between border-b border-slate-200 px-6 py-5 ">
 <div>
 <h1 class="text-2xl font-semibold text-slate-900 ">{{ __('Análisis financiero') }}</h1>
 <p class="mt-1 text-sm text-slate-600 ">{{ __('Indicadores para revisar cartera y conciliaciones.') }}</p>
 </div>
 </header>
 <div class="grid gap-4 p-6 sm:grid-cols-3">
 <x-dashboard.stat :label="__('Facturas pendientes')" :value="$metrics['pendingCount']" icon="receipt" />
 <x-dashboard.stat :label="__('Pagos por conciliar')" :value="\App\Support\Formatters\MoneyFormatter::pen($metrics['recentPayments'])" icon="alert-circle" />
 <x-dashboard.stat :label="__('Facturas vencidas')" :value="$metrics['overdueCount']" icon="badge-check" />
 </div>
 </article>

 <article class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Recomendaciones') }}</h2>
 </header>
 <div class="space-y-3 p-6 text-sm text-slate-600 ">
 <p>{{ __('Prioriza conciliaciones de pagos mayores a S/ 10,000.') }}</p>
 <p>{{ __('Comunica hallazgos a finanzas para bloqueo preventivo si es necesario.') }}</p>
 </div>
 </article>
 </section>

 <section class="grid gap-6 lg:grid-cols-2">
 <article class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Facturas a revisar') }}</h2>

 </header>
        <div class="overflow-x-auto">
          <table class="table table-md">
            <thead>
              <tr class="table-row">
                <th class="table-header">{{ __('Número') }}</th>
                <th class="table-header">{{ __('Cliente') }}</th>
                <th class="table-header">{{ __('Monto') }}</th>
                <th class="table-header">{{ __('Días vencidos') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($outstandingInvoices as $invoice)
                <tr class="table-row table-row-hover">
                  <td class="table-cell text-sm font-medium text-slate-900 ">{{ $invoice->invoice_number ?? '—' }}</td>
                  <td class="table-cell text-sm text-slate-600 ">
                    {{ optional($invoice->client)->business_name ?? optional($invoice->client)->contact_name ?? '—' }}
                  </td>
                  <td class="table-cell text-sm text-slate-600 ">{{ \App\Support\Formatters\MoneyFormatter::pen($invoice->total) }}</td>
                  <td class="table-cell text-sm text-slate-600 ">
                    @php($days = optional($invoice->due_date)?->diffInDays(now(), false))
                    {{ $days === null ? '—' : max($days, 0) }}
                  </td>
                </tr>
              @empty
                <tr class="table-row">
                  <td colspan="4" class="table-empty">{{ __('Sin facturas críticas por revisar.') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
 </article>

 <article class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Pagos pendientes de conciliación') }}</h2>
 </header>
        <div class="overflow-x-auto">
          <table class="table table-md">
            <thead>
              <tr class="table-row">
                <th class="table-header">{{ __('Referencia') }}</th>
                <th class="table-header">{{ __('Cliente') }}</th>
                <th class="table-header">{{ __('Monto') }}</th>
                <th class="table-header">{{ __('Recibido') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($latestPayments as $payment)
                <tr class="table-row table-row-hover">
                  <td class="table-cell text-sm font-medium text-slate-900 ">{{ $payment->reference ?? '—' }}</td>
                  <td class="table-cell text-sm text-slate-600 ">
                    {{ optional($payment->invoice?->client)->business_name ?? optional($payment->invoice?->client)->contact_name ?? '—' }}
                  </td>
                  <td class="table-cell text-sm text-slate-600 ">{{ \App\Support\Formatters\MoneyFormatter::pen($payment->amount) }}</td>
                  <td class="table-cell text-sm text-slate-600 ">{{ optional($payment->paid_at)?->format('d/m/Y') ?? '—' }}</td>
                </tr>
              @empty
                <tr class="table-row">
                  <td colspan="4" class="table-empty">{{ __('Todo concilia al día, excelente trabajo.') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
 </article>
 </section>


 <section class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Buenas prácticas de analistas') }}</h2>
 </header>
 <div class="space-y-3 p-6 text-sm text-slate-600 ">
 <p>{{ __('Limitar acceso a edición mitiga riesgos de fraude interno.') }}</p>
 <p>{{ __('Reporta anomalías al área de finanzas para acciones correctivas.') }}</p>
 </div>
 </section>
</div>
