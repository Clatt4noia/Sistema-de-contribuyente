<div class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-token">{{ __('Analíticas de caja') }}</h1>
            <p class="mt-1 text-sm text-token-muted">
                {{ __('Explora el comportamiento de tus ingresos y egresos para identificar tendencias y oportunidades de ahorro.') }}
            </p>
        </div>

        <a href="{{ route('finance.transactions.index') }}" class="btn btn-ghost w-full md:w-auto">
            {{ __('Volver al control de caja') }}
        </a>
    </div>

    <div class="surface-card rounded-2xl border border-token bg-elevated p-4 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-token">{{ __('Resumen del periodo') }}</h2>
                <p class="text-sm text-token-muted">{{ $summary['range_label'] ?? '' }}</p>
            </div>
            <div class="md:w-60">
                <label for="analytics-range" class="sr-only">{{ __('Rango de análisis') }}</label>
                <select id="analytics-range" wire:model="range" class="form-control">
                    @foreach ($rangeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-success-soft bg-success-weak p-4">
                <p class="text-sm font-medium text-success-strong">{{ __('Ingresos') }}</p>
                <p class="mt-2 text-2xl font-semibold text-success-strong">
                    {{ \App\Support\Formatters\MoneyFormatter::pen($summary['income'] ?? 0) }}
                </p>
            </div>
            <div class="rounded-2xl border border-danger-soft bg-danger-weak p-4">
                <p class="text-sm font-medium text-danger-strong">{{ __('Egresos') }}</p>
                <p class="mt-2 text-2xl font-semibold text-danger-strong">
                    {{ \App\Support\Formatters\MoneyFormatter::pen($summary['expense'] ?? 0) }}
                </p>
            </div>
            <div class="rounded-2xl border border-token bg-elevated p-4">
                <p class="text-sm font-medium text-token-muted">{{ __('Balance') }}</p>
                <p class="mt-2 text-2xl font-semibold text-token">
                    {{ \App\Support\Formatters\MoneyFormatter::pen($summary['balance'] ?? 0) }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="surface-card rounded-2xl border border-token bg-elevated p-6 shadow-sm">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-token">{{ __('Tendencia mensual') }}</h2>
                        <p class="text-sm text-token-muted">{{ __('Comparativo de los últimos 12 meses.') }}</p>
                    </div>
                </div>

                <div class="mt-4 overflow-hidden rounded-xl border border-token">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th class="table-header">{{ __('Mes') }}</th>
                                <th class="table-header">{{ __('Ingresos') }}</th>
                                <th class="table-header">{{ __('Egresos') }}</th>
                                <th class="table-header">{{ __('Balance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($monthly as $month)
                                <tr class="table-row">
                                    <td class="table-cell text-sm text-token">{{ $month['label'] }}</td>
                                    <td class="table-cell text-sm font-medium text-success-strong">
                                        {{ \App\Support\Formatters\MoneyFormatter::pen($month['income']) }}
                                    </td>
                                    <td class="table-cell text-sm font-medium text-danger-strong">
                                        {{ \App\Support\Formatters\MoneyFormatter::pen($month['expense']) }}
                                    </td>
                                    <td class="table-cell text-sm font-semibold text-token">
                                        {{ \App\Support\Formatters\MoneyFormatter::pen($month['balance']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="table-empty">{{ __('No hay datos suficientes para mostrar.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="surface-card rounded-2xl border border-token bg-elevated p-6 shadow-sm">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-token">{{ __('Ritmo semanal') }}</h2>
                        <p class="text-sm text-token-muted">{{ __('Evolución de ingresos y egresos durante las últimas 8 semanas.') }}</p>
                    </div>
                </div>

                <div class="mt-4 overflow-hidden rounded-xl border border-token">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th class="table-header">{{ __('Semana') }}</th>
                                <th class="table-header">{{ __('Rango') }}</th>
                                <th class="table-header">{{ __('Ingresos') }}</th>
                                <th class="table-header">{{ __('Egresos') }}</th>
                                <th class="table-header">{{ __('Balance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($weekly as $week)
                                <tr class="table-row">
                                    <td class="table-cell text-sm text-token">{{ $week['label'] }}</td>
                                    <td class="table-cell text-sm text-token-muted">{{ $week['range'] }}</td>
                                    <td class="table-cell text-sm font-medium text-success-strong">
                                        {{ \App\Support\Formatters\MoneyFormatter::pen($week['income']) }}
                                    </td>
                                    <td class="table-cell text-sm font-medium text-danger-strong">
                                        {{ \App\Support\Formatters\MoneyFormatter::pen($week['expense']) }}
                                    </td>
                                    <td class="table-cell text-sm font-semibold text-token">
                                        {{ \App\Support\Formatters\MoneyFormatter::pen($week['balance']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="table-empty">{{ __('No hay datos suficientes para mostrar.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="surface-card rounded-2xl border border-token bg-elevated p-6 shadow-sm">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-token">{{ __('Pulso diario') }}</h2>
                        <p class="text-sm text-token-muted">{{ __('Comportamiento de los últimos 14 días.') }}</p>
                    </div>
                </div>

                <div class="mt-4 overflow-hidden rounded-xl border border-token">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th class="table-header">{{ __('Día') }}</th>
                                <th class="table-header">{{ __('Ingresos') }}</th>
                                <th class="table-header">{{ __('Egresos') }}</th>
                                <th class="table-header">{{ __('Balance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daily as $day)
                                <tr class="table-row">
                                    <td class="table-cell text-sm text-token">{{ $day['label'] }}</td>
                                    <td class="table-cell text-sm font-medium text-success-strong">
                                        {{ \App\Support\Formatters\MoneyFormatter::pen($day['income']) }}
                                    </td>
                                    <td class="table-cell text-sm font-medium text-danger-strong">
                                        {{ \App\Support\Formatters\MoneyFormatter::pen($day['expense']) }}
                                    </td>
                                    <td class="table-cell text-sm font-semibold text-token">
                                        {{ \App\Support\Formatters\MoneyFormatter::pen($day['balance']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="table-empty">{{ __('No hay datos suficientes para mostrar.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="surface-card rounded-2xl border border-token bg-elevated p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-token">{{ __('Categorías destacadas') }}</h2>
                <p class="mt-1 text-sm text-token-muted">
                    {{ __('Top 5 categorías por volumen dentro del rango seleccionado.') }}
                </p>

                <div class="mt-4 space-y-4">
                    @forelse ($categorySplit as $category)
                        <div class="rounded-xl border border-token bg-surface p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-token">{{ $category['category'] ?: __('Sin categoría') }}</p>
                                    <p class="text-xs text-token-muted">{{ __('Balance neto') }}: {{ \App\Support\Formatters\MoneyFormatter::pen($category['balance']) }}</p>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full bg-accent-soft px-3 py-1 text-xs font-semibold text-accent">
                                    {{ __('Ingresos') }} {{ \App\Support\Formatters\MoneyFormatter::pen($category['income']) }}
                                </span>
                            </div>
                            <p class="mt-3 text-xs text-token-muted">
                                {{ __('Egresos') }} {{ \App\Support\Formatters\MoneyFormatter::pen($category['expense']) }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-token-muted">{{ __('No hay categorías destacadas para el periodo.') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="surface-card rounded-2xl border border-token bg-elevated p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-token">{{ __('Sugerencias de seguimiento') }}</h2>
                <p class="mt-1 text-sm text-token-muted">
                    {{ __('Usa estas métricas para definir objetivos y alertas personales.') }}
                </p>
                <ul class="mt-4 list-inside list-disc space-y-2 text-sm text-token-muted">
                    <li>{{ __('Establece un objetivo mensual de ahorro basado en el balance promedio de los últimos meses.') }}</li>
                    <li>{{ __('Monitorea las semanas con mayor egreso para anticipar compromisos futuros.') }}</li>
                    <li>{{ __('Registra notas en las transacciones atípicas para recordar su origen.') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
