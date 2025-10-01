<div class="space-y-6">
    <section class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <article class="surface-card">
            <header class="flex items-center justify-between border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Seguimiento logístico') }}</h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Estado actual de órdenes, asignaciones y flota disponible.') }}</p>
                </div>
            </header>
            <div class="grid gap-4 p-6 sm:grid-cols-3">
                <x-dashboard.stat :label="__('Órdenes totales')" :value="$ordersSummary['total']" icon="package" />
                <x-dashboard.stat :label="__('En tránsito')" :value="$ordersSummary['inTransit']" icon="navigation" />
                <x-dashboard.stat :label="__('Programadas')" :value="$ordersSummary['scheduled']" icon="calendar" />
            </div>
        </article>

        <article class="surface-card">
            <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Disponibilidad de flota') }}</h2>
            </header>
            <div class="space-y-4 p-6">
                <p class="text-4xl font-semibold text-emerald-500 dark:text-emerald-300">{{ number_format($availableTrucks) }}</p>
                <p class="text-sm text-slate-600 dark:text-slate-400">{{ __('Unidades listas para asignar.') }}</p>
            </div>
        </article>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="surface-card">
            <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Próximas asignaciones') }}</h2>
            </header>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">{{ __('Camión') }}</th>
                            <th class="px-4 py-3">{{ __('Chofer') }}</th>
                            <th class="px-4 py-3">{{ __('Orden') }}</th>
                            <th class="px-4 py-3">{{ __('Inicio') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                        @forelse ($upcomingAssignments as $assignment)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ optional($assignment->truck)->plate_number ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($assignment->driver)->full_name ?? optional($assignment->driver)->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($assignment->order)->reference ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($assignment->start_date)?->format('d/m/Y H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Sin asignaciones próximas.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="surface-card">
            <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Órdenes recientes') }}</h2>
            </header>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">{{ __('Referencia') }}</th>
                            <th class="px-4 py-3">{{ __('Cliente') }}</th>
                            <th class="px-4 py-3">{{ __('Estado') }}</th>
                            <th class="px-4 py-3">{{ __('Recolección') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                        @forelse ($recentOrders as $order)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $order->reference ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($order->client)->business_name ?? optional($order->client)->contact_name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">{{ $order->status ?? __('pendiente') }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($order->pickup_date)?->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Registra órdenes para visualizar actividad reciente.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <section class="surface-card">
        <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Por qué separar paneles') }}</h2>
        </header>
        <div class="space-y-3 p-6 text-sm text-slate-600 dark:text-slate-300">
            <p>{{ __('Este panel concentra indicadores de rutas y disponibilidad para el equipo logístico. Al separar los dashboards evitamos que finanzas o clientes vean datos operativos que no les competen.') }}</p>
            <p>{{ __('Cada módulo aplica autorización en backend; aunque un usuario conozca la URL, el sistema denegará el acceso si su rol no corresponde.') }}</p>
        </div>
    </section>
</div>
