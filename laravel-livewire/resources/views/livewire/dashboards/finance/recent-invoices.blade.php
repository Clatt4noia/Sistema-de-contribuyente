<section class="surface-card">
    <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Facturas recientes') }}</h2>
    </header>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3">{{ __('Número') }}</th>
                    <th class="px-4 py-3">{{ __('Cliente') }}</th>
                    <th class="px-4 py-3">{{ __('Estado') }}</th>
                    <th class="px-4 py-3">{{ __('Monto') }}</th>
                    <th class="px-4 py-3">{{ __('Emisión') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                @forelse ($this->invoices as $invoice)
                    <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $invoice->invoice_number ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                            {{ optional($invoice->client)->business_name ?? optional($invoice->client)->contact_name ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                                {{ $invoice->status ? __($invoice->status) : __('pendiente') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $invoice->formatted_total }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($invoice->issue_date)?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Carga facturas para monitorear el flujo de ingresos.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
