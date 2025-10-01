<div class="space-y-6">
    <section class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <article class="surface-card">
            <header class="flex items-center justify-between border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Salud de la flota') }}</h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Disponibilidad, mantenimientos y documentos clave.') }}</p>
                </div>
            </header>
            <div class="grid gap-4 p-6 sm:grid-cols-3">
                <x-dashboard.stat :label="__('Disponibles')" :value="$fleetStats['available']" icon="truck" />
                <x-dashboard.stat :label="__('En mantenimiento')" :value="$fleetStats['inMaintenance']" icon="timer" />
                <x-dashboard.stat :label="__('Documentos por vencer')" :value="$fleetStats['expiringDocuments']" icon="alert-circle" />
            </div>
        </article>

        <article class="surface-card">
            <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Checklist rápido') }}</h2>
            </header>
            <div class="space-y-3 p-6 text-sm text-slate-600 dark:text-slate-300">
                <p>{{ __('Confirma SOAT y revisiones técnicas antes de liberar unidades.') }}</p>
                <p>{{ __('Coordina con logística asignaciones según capacidad disponible.') }}</p>
            </div>
        </article>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="surface-card">
            <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Mantenimientos próximos') }}</h2>
            </header>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">{{ __('Camión') }}</th>
                            <th class="px-4 py-3">{{ __('Tipo') }}</th>
                            <th class="px-4 py-3">{{ __('Fecha') }}</th>
                            <th class="px-4 py-3">{{ __('Responsable') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                        @forelse ($upcomingMaintenances as $maintenance)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ optional($maintenance->truck)->plate_number ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $maintenance->type ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($maintenance->scheduled_at)?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($maintenance->responsible)->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No hay mantenimientos programados.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="surface-card">
            <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Documentos próximos a vencer') }}</h2>
            </header>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">{{ __('Camión') }}</th>
                            <th class="px-4 py-3">{{ __('Documento') }}</th>
                            <th class="px-4 py-3">{{ __('Vencimiento') }}</th>
                            <th class="px-4 py-3">{{ __('Estado') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                        @forelse ($expiringDocuments as $document)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ optional($document->truck)->plate_number ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $document->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($document->expires_at)?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">{{ $document->status_label ?? __('pendiente') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Registra documentos para mantener trazabilidad.') }}</td>
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
            <p>{{ __('Operaciones de flota requieren foco en disponibilidad y cumplimiento legal.') }}</p>
            <p>{{ __('La segmentación evita que logística altere mantenimientos sin aprobación.') }}</p>
        </div>
    </section>
</div>

