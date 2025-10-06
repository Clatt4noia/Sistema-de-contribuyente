<div class="space-y-6">
    <section class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <article class="surface-card">
            <header class="flex items-center justify-between border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Resumen financiero') }}</h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Cobros, pagos y facturación para la toma de decisiones.') }}</p>
                </div>
            </header>
            <div class="grid gap-4 p-6 sm:grid-cols-3">
                <x-dashboard.stat :label="__('Facturado mes actual')" :value="$billingMetrics['currentMonth']" icon="receipt" />
                <x-dashboard.stat :label="__('Pagos pendientes')" :value="$billingMetrics['pendingPayments']" icon="alert-circle" />
                <x-dashboard.stat :label="__('Cobrado 30 días')" :value="$billingMetrics['lastThirtyDays']" icon="banknote" />
            </div>
        </article>

        <article class="surface-card">
            <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Alertas') }}</h2>
            </header>
            <div class="space-y-3 p-6 text-sm text-slate-600 dark:text-slate-300">
                <p>{{ __('Revisa facturas con más de 15 días vencidas y coordina con logística antes de retener servicios.') }}</p>
                <p>{{ __('Activa recordatorios automáticos para clientes con historial de mora.') }}</p>
            </div>
        </article>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="surface-card">
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
                            <th class="px-4 py-3">{{ __('Emisión') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                        @forelse ($recentInvoices as $invoice)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $invoice->number ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($invoice->client)->business_name ?? optional($invoice->client)->contact_name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">{{ $invoice->status ? __($invoice->status) : __('pendiente') }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($invoice->issued_at)?->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Carga facturas para monitorear el flujo de ingresos.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="surface-card">
            <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Pagos recientes') }}</h2>
            </header>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">{{ __('Referencia') }}</th>
                            <th class="px-4 py-3">{{ __('Cliente') }}</th>
                            <th class="px-4 py-3">{{ __('Monto') }}</th>
                            <th class="px-4 py-3">{{ __('Fecha') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                        @forelse ($recentPayments as $payment)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $payment->reference ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($payment->client)->business_name ?? optional($payment->client)->contact_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $payment->amount?->formatCurrency('PEN') ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($payment->paid_at)?->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Registra pagos para analizar liquidez.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <section class="surface-card">
        <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Racional de separación') }}</h2>
        </header>
        <div class="space-y-3 p-6 text-sm text-slate-600 dark:text-slate-300">
            <p>{{ __('Finanzas ve métricas agregadas sin exponerse a operaciones en tránsito, reduciendo el riesgo de fraude interno.') }}</p>
            <p>{{ __('Los accesos dependen del rol y se validan en backend con políticas.') }}</p>
        </div>
    </section>
</div>

