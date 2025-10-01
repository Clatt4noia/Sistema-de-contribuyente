<div class="space-y-6">
    <section class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <article class="surface-card">
            <header class="flex items-center justify-between border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Bienvenido a tu portal logístico') }}</h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Consulta el estado de tus servicios en tiempo real.') }}</p>
                </div>
            </header>
            <div class="grid gap-4 p-6 sm:grid-cols-3">
                <x-dashboard.stat :label="__('Servicios activos')" :value="$serviceStats['active']" icon="navigation" />
                <x-dashboard.stat :label="__('Pendientes por programar')" :value="$serviceStats['scheduled']" icon="calendar" />
                <x-dashboard.stat :label="__('Facturas pendientes')" :value="$serviceStats['pendingInvoices']" icon="receipt" />
            </div>
        </article>

        <article class="surface-card">
            <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Notificaciones recientes') }}</h2>
            </header>
            <ul class="divide-y divide-slate-200 p-6 text-sm dark:divide-slate-800">
                @forelse ($notifications as $notification)
                    <li class="py-3">
                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ $notification['title'] }}</p>
                        <p class="text-slate-600 dark:text-slate-300">{{ $notification['message'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $notification['timestamp'] }}</p>
                    </li>
                @empty
                    <li class="py-3 text-slate-500 dark:text-slate-400">{{ __('Sin notificaciones nuevas.') }}</li>
                @endforelse
            </ul>
        </article>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="surface-card">
            <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Servicios en curso') }}</h2>
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
                        @forelse ($activeOrders as $order)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $order->reference ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $order->origin ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $order->destination ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">{{ $order->status ?? __('pendiente') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No tienes servicios activos en este momento.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="surface-card">
            <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Facturas recientes') }}</h2>
            </header>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">{{ __('Número') }}</th>
                            <th class="px-4 py-3">{{ __('Monto') }}</th>
                            <th class="px-4 py-3">{{ __('Estado') }}</th>
                            <th class="px-4 py-3">{{ __('Emisión') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                        @forelse ($clientInvoices as $invoice)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $invoice->number ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $invoice->amount?->formatCurrency('PEN') ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">{{ $invoice->status ?? __('pendiente') }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($invoice->issued_at)?->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No hay facturas registradas aún.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <section class="surface-card">
        <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Transparencia y seguridad') }}</h2>
        </header>
        <div class="space-y-3 p-6 text-sm text-slate-600 dark:text-slate-300">
            <p>{{ __('Separar los paneles evita que otros clientes vean información sensible.') }}</p>
            <p>{{ __('Tu cuenta sólo puede visualizar servicios y facturas que te pertenecen.') }}</p>
        </div>
    </section>
</div>
