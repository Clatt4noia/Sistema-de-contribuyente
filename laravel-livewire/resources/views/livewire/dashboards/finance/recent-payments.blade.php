<section class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Pagos recientes') }}</h2>
 </header>

 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-slate-200 text-sm ">
 <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 ">
 <tr>
 <th class="px-4 py-3">{{ __('Referencia') }}</th>
 <th class="px-4 py-3">{{ __('Cliente') }}</th>
 <th class="px-4 py-3">{{ __('Monto') }}</th>
 <th class="px-4 py-3">{{ __('Fecha') }}</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-slate-100 bg-white ">
 @forelse ($this->payments as $payment)
 <tr class="transition hover:bg-slate-50 ">
 <td class="px-4 py-3 font-medium text-slate-900 ">{{ $payment->reference ?? '—' }}</td>
 <td class="px-4 py-3 text-slate-600 ">
 {{ optional($payment->invoice?->client)->business_name ?? optional($payment->invoice?->client)->contact_name ?? '—' }}
 </td>
 <td class="px-4 py-3 text-slate-600 ">{{ $payment->formatted_amount }}</td>
 <td class="px-4 py-3 text-slate-600 ">{{ optional($payment->paid_at)?->format('d/m/Y') ?? '—' }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 ">{{ __('Registra pagos para analizar liquidez.') }}</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
</section>
