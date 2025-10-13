<section class="grid gap-6 lg:grid-cols-[2fr_1fr]">
 <article class="surface-card">
 <header class="flex items-center justify-between border-b border-slate-200 px-6 py-5 ">
 <div>
 <h1 class="text-2xl font-semibold text-slate-900 ">{{ __('Resumen financiero') }}</h1>
 <p class="mt-1 text-sm text-slate-600 ">{{ __('Cobros, pagos y facturación para la toma de decisiones.') }}</p>
 </div>
 </header>

 <div class="grid gap-4 p-6 sm:grid-cols-3">
 <x-dashboard.stat :label="__('Facturado mes actual')" :value="$this->metrics['current_month']" icon="receipt" />
 <x-dashboard.stat :label="__('Pagos pendientes')" :value="$this->metrics['pending_payments']" icon="alert-circle" />
 <x-dashboard.stat :label="__('Cobrado últimos 30 días')" :value="$this->metrics['last_thirty_days']" icon="banknote" />
 </div>
 </article>

 <article class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Alertas') }}</h2>
 </header>
 <div class="space-y-3 p-6 text-sm text-slate-600 ">
 <p>{{ __('Revisa facturas con más de 15 días vencidas y coordina con logística antes de retener servicios.') }}</p>
 <p>{{ __('Activa recordatorios automáticos para clientes con historial de mora.') }}</p>
 </div>
 </article>
</section>
