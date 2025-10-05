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
            <div class="border-t border-slate-200/70 px-6 py-6 dark:border-slate-800/60">
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <div class="xl:col-span-1">
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Distribución de la flota') }}</h3>
                        <div class="relative h-60">
                            <canvas id="fleet-status-chart" aria-label="{{ __('Distribución de estados de la flota') }}" role="img"></canvas>
                        </div>
                    </div>

                    <div class="md:col-span-2 xl:col-span-1">
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Mantenimientos por mes') }}</h3>
                        <div class="relative h-60">
                            <canvas id="maintenance-trend-chart" aria-label="{{ __('Tendencia de mantenimientos programados') }}" role="img"></canvas>
                        </div>
                    </div>

                    <div class="md:col-span-2 xl:col-span-1">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Asignaciones por chofer') }}</h3>
                            <span class="rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-semibold text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-200">{{ __('Promedio: :value', ['value' => $assignmentsAverage]) }}</span>
                        </div>
                        <div class="relative mt-4 h-60">
                            <canvas id="driver-assignments-chart" aria-label="{{ __('Top de asignaciones por chofer') }}" role="img"></canvas>
                        </div>
                        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">{{ __('Basado en los últimos 90 días de operaciones.') }}</p>
                    </div>
                </div>
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
                            <th class="px-4 py-3">{{ __('Recurso') }}</th>
                            <th class="px-4 py-3">{{ __('Documento') }}</th>
                            <th class="px-4 py-3">{{ __('Vencimiento') }}</th>
                            <th class="px-4 py-3">{{ __('Estado') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                        @php
                            $badgeStyles = [
                                \App\Models\Document::STATUS_VALID => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200',
                                \App\Models\Document::STATUS_WARNING => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
                                \App\Models\Document::STATUS_EXPIRED => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200',
                            ];
                        @endphp
                        @forelse ($expiringDocuments as $document)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $document->resource_label }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $document->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($document->expires_at)?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badgeStyles[$document->status] ?? $badgeStyles[\App\Models\Document::STATUS_WARNING] }}">{{ $document->status_label ?? __('Pendiente') }}</span>
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

@php
    $chartPayloads = [
        'status' => $statusChart,
        'maintenance' => $maintenanceTrend,
        'assignments' => $assignmentsChart,
    ];
@endphp

<script>
    document.addEventListener('livewire:init', () => {
        const chartPayloads = @json($chartPayloads);

        const chartFactory = () => {
            if (typeof window === 'undefined' || typeof window.Chart === 'undefined') {
                return;
            }

            const registryKey = '__fleetDashboardCharts';
            window[registryKey] = window[registryKey] || {};
            const registry = window[registryKey];

            const createOrUpdate = (id, config) => {
                const canvas = document.getElementById(id);
                if (!canvas) {
                    return;
                }

                const context = canvas.getContext('2d');

                if (registry[id]) {
                    registry[id].data = config.data;
                    registry[id].options = config.options || {};
                    registry[id].update();
                    return;
                }

                registry[id] = new window.Chart(context, config);
            };

            createOrUpdate('fleet-status-chart', {
                type: 'doughnut',
                data: chartPayloads.status,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                            },
                        },
                    },
                    cutout: '60%',
                },
            });

            createOrUpdate('maintenance-trend-chart', {
                type: 'line',
                data: chartPayloads.maintenance,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.2)',
                            },
                        },
                        x: {
                            grid: {
                                display: false,
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                },
            });

            createOrUpdate('driver-assignments-chart', {
                type: 'bar',
                data: chartPayloads.assignments,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.2)',
                            },
                        },
                        x: {
                            grid: {
                                display: false,
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                },
            });
        };

        chartFactory();

        Livewire.hook('message.processed', (message, component) => {
            if (component.fingerprint.name === 'dashboards.fleet-dashboard') {
                chartFactory();
            }
        });
    });
</script>

