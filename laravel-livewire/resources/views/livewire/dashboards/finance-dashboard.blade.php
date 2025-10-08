<div class="space-y-6">
    <livewire:dashboards.finance.overview-stats />

    <section class="grid gap-6 lg:grid-cols-2">
        <livewire:dashboards.finance.recent-invoices />
        <livewire:dashboards.finance.recent-payments />
    </section>

    <section class="surface-card">
        <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Buenas prácticas de control financiero') }}</h2>
        </header>
        <div class="space-y-3 p-6 text-sm text-slate-600 dark:text-slate-300">
            <p>{{ __('Segrega responsabilidades entre finanzas y operaciones para reducir riesgos de fraude interno.') }}</p>
            <p>{{ __('Configura alertas automáticas sobre desvíos de presupuesto para reaccionar a tiempo.') }}</p>
        </div>
    </section>
</div>
