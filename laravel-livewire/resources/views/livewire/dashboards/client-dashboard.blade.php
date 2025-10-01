<section class="grid gap-6 lg:grid-cols-[2fr_1fr]">
    <article class="surface-card">
        <header class="flex items-center justify-between border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Estado de mis servicios') }}</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Revisa el progreso de tus órdenes y la facturación asociada.') }}</p>
            </div>
        </header>
        <div class="grid gap-4 p-6 sm:grid-cols-3">
            <x-dashboard.stat :label="__('Órdenes registradas')" :value="$metrics['orders']" icon="package" />
            <x-dashboard.stat :label="__('Facturas emitidas')" :value="$metrics['invoices']" icon="receipt" />
            <x-dashboard.stat :label="__('Facturas por pagar')" :value="$metrics['openInvoices']" icon="alert-circle" />
        </div>
    </article>

    <article class="surface-card">
        <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Soporte dedicado') }}</h2>
        </header>
        <div class="space-y-3 p-6 text-sm text-slate-600 dark:text-slate-300">
            <p>{{ __('Para consultas adicionales contacta a nuestro equipo en:') }}</p>
            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $contactEmail }}</p>
            <p>{{ __('Solo tu empresa puede ver esta información; mantenemos segregación completa respecto a otros clientes.') }}</p>
        </div>
    </article>
</section>

<section class="surface-card">
    <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Órdenes recientes') }}</h2>
    </header>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3">{{ __('Referencia') }}</th>
                    <th class="px-4 py-3">{{ __('Origen') }}</th>
                    <th class="px-4 py-3">{{ __('Destino') }}</th>
                    <th class="px-4 py-3">{{ __('Estado') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                @forelse ($orders as $order)
                    <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $order->reference ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $order->origin ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $order->destination ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-500/20 dark:text-sky-200">{{ $order->status ?? __('pendiente') }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No se encontraron órdenes asociadas a tu cuenta.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="surface-card">
    <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Facturas más recientes') }}</h2>
    </header>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3">{{ __('Número') }}</th>
                    <th class="px-4 py-3">{{ __('Emisión') }}</th>
                    <th class="px-4 py-3">{{ __('Vencimiento') }}</th>
                    <th class="px-4 py-3">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                @forelse ($invoices as $invoice)
                    <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $invoice->invoice_number ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($invoice->issue_date)?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($invoice->due_date)?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">S/ {{ number_format($invoice->total ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Aún no se emiten facturas para tu organización.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="surface-card">
    <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Seguridad y privacidad') }}</h2>
    </header>
    <div class="space-y-3 p-6 text-sm text-slate-600 dark:text-slate-300">
        <p>{{ __('Solo los usuarios autorizados de CARLOS GABRIEL TRANSPORTE pueden acceder a esta vista con tus datos.') }}</p>
        <p>{{ __('Si detectas información incorrecta, comunícate de inmediato para cumplir la Ley 29733 de protección de datos personales.') }}</p>
    </div>
</section>
