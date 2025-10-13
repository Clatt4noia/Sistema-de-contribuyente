<div class="space-y-6">

    <!-- Visión general de la operación -->
    <section class="surface-card w-full max-w-6xl mx-auto">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 px-6 py-5">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">
                    {{ __('Visión general de la operación') }}
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('Consolida logística, flota y finanzas para tomar decisiones en segundos.') }}
                </p>
            </div>
        </header>
        <div class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4">
            <x-dashboard.stat :label="__('Camiones registrados')" :value="$fleetTotals['trucks']" icon="truck" />
            <x-dashboard.stat :label="__('Asignaciones activas')" :value="$fleetTotals['assignments']" icon="route" />
            <x-dashboard.stat :label="__('Servicios logísticos')" :value="$ordersCount" icon="navigation" />
            <x-dashboard.stat :label="__('Clientes corporativos')" :value="$clientsCount" icon="building" />
        </div>
    </section>

    <!-- Accesos rápidos -->
    <section class="surface-card w-full max-w-6xl mx-auto">
        <header class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-lg font-semibold text-slate-900">
                {{ __('Accesos rápidos') }}
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('Llega directo a los módulos críticos según la prioridad del día.') }}
            </p>
        </header>
        <nav class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-3 text-sm font-semibold text-slate-600">
            <a href="{{ route('dashboards.logistics') }}" class="group flex items-center justify-between rounded-xl border border-slate-200 px-5 py-4 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-white">
                <span>{{ __('Panel logístico') }}</span>
                <x-dynamic-component :component="'flux.icon.arrow-right'" class="size-4 text-indigo-500 group-hover:translate-x-0.5" />
            </a>

            <a href="{{ route('dashboards.fleet') }}" class="group flex items-center justify-between rounded-xl border border-slate-200 px-5 py-4 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-white">
                <span>{{ __('Panel de flota') }}</span>
                <x-dynamic-component :component="'flux.icon.arrow-right'" class="size-4 text-indigo-500 group-hover:translate-x-0.5" />
            </a>

            <a href="{{ route('dashboards.finance') }}" class="group flex items-center justify-between rounded-xl border border-slate-200 px-5 py-4 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-white">
                <span>{{ __('Panel financiero') }}</span>
                <x-dynamic-component :component="'flux.icon.arrow-right'" class="size-4 text-indigo-500 group-hover:translate-x-0.5" />
            </a>
        </nav>
    </section>

</div>
