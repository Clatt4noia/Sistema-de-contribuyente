<section class="grid gap-6 lg:grid-cols-[2fr_1fr]">
    <article class="surface-card">
        <header class="flex items-center justify-between border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Monitoreo de cartera') }}</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Conciliaciones pendientes y pagos recientes para analistas financieros.') }}</p>
            </div>
        </header>
        <div class="grid gap-4 p-6 sm:grid-cols-3">
            <x-dashboard.stat :label="__('Facturas pendientes')" :value="$metrics['pendingCount']" icon="alert-circle" />
            <x-dashboard.stat :label="__('Facturas vencidas')" :value="$metrics['overdueCount']" icon="timer" />
            <x-dashboard.stat :label="__('Pagado últimos 30 días (S/)')" :value="$metrics['recentPayments']" icon="banknote" decimals="2" />
        </div>
    </article>

    <article class="surface-card">
        <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Buenas prácticas de conciliación') }}</h2>
        </header>
        <div class="space-y-3 p-6 text-sm text-slate-600 dark:text-slate-300">
            <p>{{ __('Valida diferencias contra extractos bancarios y registra evidencias en el sistema.') }}</p>
            <p>{{ __('Escala casos críticos al finance manager para aprobación.') }}</p>
        </div>
    </article>
</section>

<section class="grid gap-6 lg:grid-cols-2">
    <article class="surface-card">
        <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Pendientes por cobrar') }}</h2>
        </header>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">{{ __('Factura') }}</th>
                        <th class="px-4 py-3">{{ __('Cliente') }}</th>
                        <th class="px-4 py-3">{{ __('Vencimiento') }}</th>
                        <th class="px-4 py-3">{{ __('Estado') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                    @forelse ($outstandingInvoices as $invoice)
                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $invoice->invoice_number ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($invoice->client)->business_name ?? optional($invoice->client)->contact_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($invoice->due_date)?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-500/20 dark:text-rose-200">{{ $invoice->status ?? __('pendiente') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No hay facturas pendientes registradas.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>

    <article class="surface-card">
        <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Pagos más recientes') }}</h2>
        </header>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">{{ __('Factura') }}</th>
                        <th class="px-4 py-3">{{ __('Fecha') }}</th>
                        <th class="px-4 py-3">{{ __('Importe') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                    @forelse ($latestPayments as $payment)
                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($payment->invoice)->invoice_number ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($payment->paid_at)?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">S/ {{ number_format($payment->amount ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Sin pagos registrados en los últimos 30 días.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="surface-card">
    <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Recordatorios clave') }}</h2>
    </header>
    <div class="space-y-3 p-6 text-sm text-slate-600 dark:text-slate-300">
        <p>{{ __('El acceso está limitado a analistas y managers para proteger los datos financieros.') }}</p>
        <p>{{ __('Si necesitas editar facturas, coordina con el finance manager ya que este rol es solo lectura.') }}</p>
    </div>
</section>
