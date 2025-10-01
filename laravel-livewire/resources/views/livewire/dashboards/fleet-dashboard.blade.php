<section class="grid gap-6 lg:grid-cols-[2fr_1fr]">
    <article class="surface-card">
        <header class="flex items-center justify-between border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Salud de la flota') }}</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Estado general de camiones, mantenimientos y documentación de conductores.') }}</p>
            </div>
        </header>
        <div class="grid gap-4 p-6 sm:grid-cols-3">
            @forelse ($statusBreakdown as $status => $count)
                @php
                    $label = $status
                        ? ucfirst(str_replace('_', ' ', $status))
                        : __('Sin estado');
                @endphp
                <x-dashboard.stat :label="$label" :value="$count" icon="truck" />
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Registra camiones para visualizar métricas.') }}</p>
            @endforelse
        </div>
    </article>

    <article class="surface-card">
        <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Documentos por vencer') }}</h2>
        </header>
        <ul class="space-y-3 p-6 text-sm text-slate-600 dark:text-slate-300">
            @forelse ($expiringLicenses as $driver)
                <li class="flex items-center justify-between">
                    <span>{{ $driver->full_name ?? $driver->name }}</span>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-200">
                        {{ optional($driver->license_expiration)?->format('d/m/Y') ?? '—' }}
                    </span>
                </li>
            @empty
                <li>{{ __('No hay licencias próximas a vencer.') }}</li>
            @endforelse
        </ul>
    </article>
</section>

<section class="surface-card">
    <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Mantenimientos programados') }}</h2>
    </header>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3">{{ __('Camión') }}</th>
                    <th class="px-4 py-3">{{ __('Tipo') }}</th>
                    <th class="px-4 py-3">{{ __('Fecha') }}</th>
                    <th class="px-4 py-3">{{ __('Estado') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                @forelse ($scheduledMaintenance as $maintenance)
                    <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ optional($maintenance->truck)->plate_number ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $maintenance->maintenance_type ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($maintenance->maintenance_date)?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">{{ $maintenance->status ?? __('programado') }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Organiza mantenimientos para verlos aquí.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="surface-card">
    <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Buenas prácticas compartidas') }}</h2>
    </header>
    <div class="space-y-3 p-6 text-sm text-slate-600 dark:text-slate-300">
        <p>{{ __('Este panel limita el alcance al equipo de flota. La segmentación evita que logística o finanzas modifiquen calendarios de mantenimiento.') }}</p>
        <p>{{ __('Sigue registrando bitácoras y mantenimientos para mantener histórico trazable y cumplir auditorías internas.') }}</p>
    </div>
</section>
