<section class="grid gap-6 lg:grid-cols-[2fr_1fr]">
    <article class="surface-card">
        <header class="flex items-center justify-between border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Control financiero integral') }}</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Resumen de facturación, pagos registrados y saldos pendientes.') }}</p>
            </div>
        </header>
        <div class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4">
            <x-dashboard.stat :label="__('Emitido')" :value="$totals['issued']" icon="receipt" decimals="2" />
            <x-dashboard.stat :label="__('Cobrado')" :value="$totals['paid']" icon="badge-check" decimals="2" />
            <x-dashboard.stat :label="__('Pendiente')" :value="$totals['pending']" icon="alert-circle" decimals="2" />
            <x-dashboard.stat :label="__('Pagos registrados')" :value="$totals['payments']" icon="banknote" decimals="2" />
        </div>
    </article>

    <article class="surface-card">
        <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Buenas prácticas financieras') }}</h2>
        </header>
        <div class="space-y-3 p-6 text-sm text-slate-600 dark:text-slate-300">
            <p>{{ __('Revisa conciliaciones diariamente y documenta discrepancias para auditoría.') }}</p>
            <p>{{ __('Mantén perfiles separados (manager/analyst) para validar la segregación de funciones.') }}</p>
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
                        <th class="px-4 py-3">{{ __('Fecha de emisión') }}</th>
                        <th class="px-4 py-3">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                    @forelse ($recentInvoices as $invoice)
                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $invoice->invoice_number ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($invoice->client)->business_name ?? optional($invoice->client)->contact_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($invoice->issue_date)?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">S/ {{ number_format($invoice->total ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Registra nuevas facturas para visualizarlas aquí.') }}</td>
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
                        <th class="px-4 py-3">{{ __('Factura') }}</th>
                        <th class="px-4 py-3">{{ __('Fecha de pago') }}</th>
                        <th class="px-4 py-3">{{ __('Importe') }}</th>
                        <th class="px-4 py-3">{{ __('Método') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                    @forelse ($recentPayments as $payment)
                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($payment->invoice)->invoice_number ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($payment->paid_at)?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">S/ {{ number_format($payment->amount ?? 0, 2) }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $payment->method ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Cuando registres pagos, aparecerán en esta tabla.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="surface-card">
    <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Notas para el equipo') }}</h2>
    </header>
    <div class="space-y-3 p-6 text-sm text-slate-600 dark:text-slate-300">
        <p>{{ __('Este dashboard está reservado a finanzas para proteger datos sensibles de facturación y pagos.') }}</p>
        <p>{{ __('Documenta políticas de aprobación doble para montos altos y activa notificaciones de vencimiento.') }}</p>
    </div>
</section>
