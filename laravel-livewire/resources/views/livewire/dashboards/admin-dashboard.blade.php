<div class="space-y-6">
    <section class="grid gap-6 lg:grid-cols-3">
        <article class="surface-card lg:col-span-2">
            <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Visión general de la operación') }}</h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Consolida logística, flota y finanzas para tomar decisiones en segundos.') }}</p>
                </div>
            </header>
            <div class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4">
                <x-dashboard.stat :label="__('Camiones registrados')" :value="$fleetTotals['trucks']" icon="truck" />
                <x-dashboard.stat :label="__('Asignaciones activas')" :value="$fleetTotals['assignments']" icon="route" />
                <x-dashboard.stat :label="__('Servicios logísticos')" :value="$ordersCount" icon="navigation" />
                <x-dashboard.stat :label="__('Clientes corporativos')" :value="$clientsCount" icon="building" />
            </div>
        </article>

        <article class="surface-card">
            <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Accesos rápidos') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('Llega directo a los módulos críticos según la prioridad del día.') }}</p>
            </header>
            <nav class="grid gap-2 p-6 text-sm font-semibold text-slate-700 dark:text-slate-200">
                <a href="{{ route('dashboards.logistics') }}" class="group flex items-center justify-between rounded-xl border border-slate-200/70 px-4 py-3 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-white dark:border-slate-800/60 dark:hover:border-indigo-500/60 dark:hover:bg-slate-900">
                    <span>{{ __('Panel logístico') }}</span>
                    <x-dynamic-component :component="'flux.icon.arrow-right'" class="size-4 text-indigo-500 group-hover:translate-x-0.5" />
                </a>
                <a href="{{ route('dashboards.fleet') }}" class="group flex items-center justify-between rounded-xl border border-slate-200/70 px-4 py-3 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-white dark:border-slate-800/60 dark:hover:border-indigo-500/60 dark:hover:bg-slate-900">
                    <span>{{ __('Panel de flota') }}</span>
                    <x-dynamic-component :component="'flux.icon.arrow-right'" class="size-4 text-indigo-500 group-hover:translate-x-0.5" />
                </a>
                <a href="{{ route('dashboards.finance') }}" class="group flex items-center justify-between rounded-xl border border-slate-200/70 px-4 py-3 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-white dark:border-slate-800/60 dark:hover:border-indigo-500/60 dark:hover:bg-slate-900">
                    <span>{{ __('Panel financiero') }}</span>
                    <x-dynamic-component :component="'flux.icon.arrow-right'" class="size-4 text-indigo-500 group-hover:translate-x-0.5" />
                </a>
            </nav>
        </article>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="surface-card">
            <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Buenas prácticas de gobierno') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    {{ __('Separamos dashboards por rol para minimizar exposición de datos sensibles y alinear las responsabilidades de cada área.') }}
                </p>
            </header>
            <div class="space-y-4 p-6 text-sm text-slate-600 dark:text-slate-300">
                <p>{{ __('Cada módulo aplica políticas OWASP 2025: autenticación fuerte, autorización backend y registros para auditoría.') }}</p>
                <p>{{ __('Los accesos rápidos combinan monitoreo y acción, reduciendo el tiempo para corregir desvíos logísticos o financieros.') }}</p>
            </div>
        </article>

        <article class="surface-card">
            <header class="border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/70">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Próximos pasos para practicantes') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('El objetivo es mantener la arquitectura modular para añadir nuevos servicios sin romper el core.') }}</p>
            </header>
            <ul class="space-y-3 p-6 text-sm text-slate-600 dark:text-slate-300">
                <li>{{ __('1. Documentar KPIs clave de cada área para alimentar estos paneles con métricas reales.') }}</li>
                <li>{{ __('2. Automatizar alertas cuando un KPI supere el umbral definido por dirección.') }}</li>
                <li>{{ __('3. Mantener pruebas de regresión para rutas y políticas por cada nuevo módulo.') }}</li>
            </ul>
        </article>
    </section>
</div>
