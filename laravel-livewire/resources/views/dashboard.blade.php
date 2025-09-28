<x-layouts.app :title="__('Dashboard')">
    <section class="grid gap-6 lg:grid-cols-[2fr,1fr]">
        <article class="flex flex-col justify-between rounded-3xl border border-cyan-100 bg-gradient-to-br from-white via-cyan-50 to-sky-100 p-8 text-slate-800 shadow-lg">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-xl space-y-2">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1 text-xs font-semibold uppercase tracking-wide text-cyan-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-cyan-500"></span>
                        Flujo activo
                    </span>
                    <h2 class="text-3xl font-semibold tracking-tight text-slate-900">Gestión de Flota</h2>
                    <p class="text-sm leading-relaxed text-slate-600">
                        Supervisa camiones, conductores y reportes con métricas en tiempo real. Toda la operación centralizada en un panel claro y fácil de leer.
                    </p>
                </div>
                <dl class="grid gap-3 text-right text-sm text-slate-600 sm:grid-cols-2">
                    <div class="rounded-2xl border border-white/70 bg-white/60 px-4 py-3">
                        <dt class="text-xs uppercase tracking-wide text-cyan-500">Camiones activos</dt>
                        <dd class="text-2xl font-semibold text-slate-900">{{ \App\Models\Truck::where('status', 'active')->count() }}</dd>
                    </div>
                    <div class="rounded-2xl border border-white/70 bg-white/60 px-4 py-3">
                        <dt class="text-xs uppercase tracking-wide text-cyan-500">Conductores</dt>
                        <dd class="text-2xl font-semibold text-slate-900">{{ \App\Models\Driver::count() }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('fleet.trucks.index') }}" class="group flex items-center justify-between rounded-2xl border border-cyan-100 bg-white/80 px-4 py-3 text-sm font-semibold text-cyan-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white">
                    <span>Camiones</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5">&rarr;</span>
                </a>
                <a href="{{ route('fleet.drivers.index') }}" class="group flex items-center justify-between rounded-2xl border border-cyan-100 bg-white/80 px-4 py-3 text-sm font-semibold text-cyan-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white">
                    <span>Conductores</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5">&rarr;</span>
                </a>
                <a href="{{ route('fleet.assignments.index') }}" class="group flex items-center justify-between rounded-2xl border border-cyan-100 bg-white/80 px-4 py-3 text-sm font-semibold text-cyan-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white">
                    <span>Asignaciones</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5">&rarr;</span>
                </a>
                <a href="{{ route('fleet.report') }}" class="group flex items-center justify-between rounded-2xl border border-cyan-100 bg-white/80 px-4 py-3 text-sm font-semibold text-cyan-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white">
                    <span>Reportes</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5">&rarr;</span>
                </a>
                <a href="{{ route('fleet.maintenance.index') }}" class="group flex items-center justify-between rounded-2xl border border-cyan-100 bg-white/80 px-4 py-3 text-sm font-semibold text-cyan-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white">
                    <span>Mantenimientos</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5">&rarr;</span>

                </a>
            </div>
        </article>

        <article class="flex flex-col rounded-3xl border border-slate-200 bg-white p-6 shadow-lg">
            <h3 class="text-xl font-semibold text-slate-900">Operaciones y Finanzas</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                Mantén el control de pedidos, clientes y facturación con accesos directos claros. Todo organizado en categorías fáciles de escanear.
            </p>
            <div class="mt-6 grid gap-3">
                <a href="{{ route('orders.index') }}" class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white">
                    <span>Pedidos</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5">&rarr;</span>
                </a>
                <a href="{{ route('clients.index') }}" class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white">
                    <span>Clientes</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5">&rarr;</span>
                </a>
                <a href="{{ route('billing.invoices.index') }}" class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white">
                    <span>Facturas</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5">&rarr;</span>
                </a>
                <a href="{{ route('billing.payments.index') }}" class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white">
                    <span>Pagos</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5">&rarr;</span>
                </a>
            </div>
            <div class="mt-6 flex items-center gap-3 text-xs font-medium uppercase tracking-wide text-slate-400">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                Información actualizada al día de hoy.
            </div>

        </article>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-lg">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Camiones disponibles</h3>
                    <p class="mt-1 text-sm text-slate-600">Últimas unidades listas para despacho.</p>
                </div>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Stock</span>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">

                        <tr>
                            <th class="px-4 py-3">Placa</th>
                            <th class="px-4 py-3">Modelo</th>
                            <th class="px-4 py-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach(\App\Models\Truck::where('status', 'available')->orderBy('plate_number')->take(5)->get() as $truck)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $truck->plate_number }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $truck->brand }} {{ $truck->model }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">

                                        Disponible
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-end">
                <a href="{{ route('fleet.trucks.index') }}" class="inline-flex items-center gap-1 rounded-full border border-cyan-200 bg-cyan-50 px-4 py-2 text-sm font-semibold text-cyan-700 transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-white">

                    Ver todos
                    <span>&rarr;</span>
                </a>
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-lg">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Mantenimientos programados</h3>
                    <p class="mt-1 text-sm text-slate-600">Próximas intervenciones agendadas.</p>
                </div>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Agenda</span>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">

                        <tr>
                            <th class="px-4 py-3">Vehículo</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Tipo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach(\App\Models\Maintenance::where('status', 'scheduled')->with('truck')->orderBy('maintenance_date')->take(5)->get() as $maintenance)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $maintenance->truck->plate_number }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $maintenance->maintenance_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $maintenance->maintenance_type }}</td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-end">
                <a href="{{ route('fleet.maintenance.index') }}" class="inline-flex items-center gap-1 rounded-full border border-cyan-200 bg-cyan-50 px-4 py-2 text-sm font-semibold text-cyan-700 transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-white">

                    Ver todos
                    <span>&rarr;</span>
                </a>
            </div>
        </article>
    </section>
</x-layouts.app>
