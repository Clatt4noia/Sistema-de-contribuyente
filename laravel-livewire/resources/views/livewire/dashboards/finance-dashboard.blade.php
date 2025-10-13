<div class="space-y-6">
 <livewire:dashboards.finance.overview-stats />

 <section class="grid gap-6 lg:grid-cols-2">
 <livewire:dashboards.finance.recent-invoices />
 <livewire:dashboards.finance.recent-payments />
 </section>

 <section class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Buenas prácticas de control financiero') }}</h2>
 </header>
 <div class="space-y-3 p-6 text-sm text-slate-600 ">
 <p>{{ __('Segrega responsabilidades entre finanzas y operaciones para reducir riesgos de fraude interno.') }}</p>
 <p>{{ __('Configura alertas automáticas sobre desvíos de presupuesto para reaccionar a tiempo.') }}</p>
 </div>
 </section>
</div>
