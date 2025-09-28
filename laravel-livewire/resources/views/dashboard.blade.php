<x-layouts.app :title="__('Dashboard')">
    <section class="grid gap-6 lg:grid-cols-3">
        <article class="rounded-3xl bg-gradient-to-r from-indigo-500 via-purple-500 to-blue-500 p-6 text-white shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight">Gestión de Flota</h2>
                    <p class="mt-2 max-w-sm text-sm text-indigo-100">
                        Supervisa camiones, conductores y reportes con métricas en tiempo real.
                    </p>
                </div>
                <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-50">
                    Flujo activo
                </span>
            </div>
            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                <a href="{{ route('fleet.trucks.index') }}" class="flex items-center justify-between rounded-2xl bg-white/15 px-4 py-3 text-sm font-semibold tracking-wide transition hover:bg-white/25">
                    <span>Camiones</span>
                    <span class="text-white/70">&rarr;</span>
                </a>
                <a href="{{ route('fleet.drivers.index') }}" class="flex items-center justify-between rounded-2xl bg-white/15 px-4 py-3 text-sm font-semibold tracking-wide transition hover:bg-white/25">
                    <span>Conductores</span>
                    <span class="text-white/70">&rarr;</span>
                </a>
                <a href="{{ route('fleet.assignments.index') }}" class="flex items-center justify-between rounded-2xl bg-white/15 px-4 py-3 text-sm font-semibold tracking-wide transition hover:bg-white/25">
                    <span>Asignaciones</span>
                    <span class="text-white/70">&rarr;</span>
                </a>
                <a href="{{ route('fleet.report') }}" class="flex items-center justify-between rounded-2xl bg-white/15 px-4 py-3 text-sm font-semibold tracking-wide transition hover:bg-white/25">
                    <span>Reportes</span>
                    <span class="text-white/70">&rarr;</span>
                </a>
                <a href="{{ route('fleet.maintenance.index') }}" class="flex items-center justify-between rounded-2xl bg-white/15 px-4 py-3 text-sm font-semibold tracking-wide transition hover:bg-white/25">
                    <span>Mantenimientos</span>
                    <span class="text-white/70">&rarr;</span>
                </a>
            </div>
        </article>

        <article class="flex flex-col rounded-3xl border border-indigo-100 bg-white/90 p-6 shadow-xl backdrop-blur-sm">
            <h3 class="text-xl font-semibold text-slate-900">Operaciones y Finanzas</h3>
            <p class="mt-2 text-sm text-slate-500">
                Mantén el control de pedidos, clientes y facturación con accesos directos claros.
            </p>
            <div class="mt-6 grid gap-3">
                <a href="{{ route('orders.index') }}" class="flex items-center justify-between rounded-2xl bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                    <span>Pedidos</span>
                    <span>&rarr;</span>
                </a>
                <a href="{{ route('clients.index') }}" class="flex items-center justify-between rounded-2xl bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                    <span>Clientes</span>
                    <span>&rarr;</span>
                </a>
                <a href="{{ route('billing.invoices.index') }}" class="flex items-center justify-between rounded-2xl bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                    <span>Facturas</span>
                    <span>&rarr;</span>
                </a>
                <a href="{{ route('billing.payments.index') }}" class="flex items-center justify-between rounded-2xl bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                    <span>Pagos</span>
                    <span>&rarr;</span>
                </a>
            </div>
            <div class="mt-6 flex items-center gap-3 text-xs font-medium uppercase tracking-wide text-slate-400">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                Información actualizada al día de hoy.
            </div>
        </article>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="rounded-3xl bg-white/95 p-6 shadow-xl ring-1 ring-slate-100 dark:bg-zinc-900/80 dark:ring-zinc-800">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Camiones disponibles</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Últimas unidades listas para despacho.</p>
                </div>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">Stock</span>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800/70 dark:text-slate-300">
                        <tr>
                            <th class="px-4 py-3">Placa</th>
                            <th class="px-4 py-3">Modelo</th>
                            <th class="px-4 py-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white/70 dark:divide-slate-800 dark:bg-transparent">
                        @foreach(\App\Models\Truck::where('status', 'available')->orderBy('plate_number')->take(5)->get() as $truck)
                            <tr class="transition hover:bg-slate-50/80 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ $truck->plate_number }}</td>
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-300">{{ $truck->brand }} {{ $truck->model }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">
                                        Disponible
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-end">
                <a href="{{ route('fleet.trucks.index') }}" class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                    Ver todos
                    <span>&rarr;</span>
                </a>
            </div>
        </article>

        <article class="rounded-3xl bg-white/95 p-6 shadow-xl ring-1 ring-slate-100 dark:bg-zinc-900/80 dark:ring-zinc-800">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Mantenimientos programados</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Próximas intervenciones agendadas.</p>
                </div>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-200">Agenda</span>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800/70 dark:text-slate-300">
                        <tr>
                            <th class="px-4 py-3">Vehículo</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Tipo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white/70 dark:divide-slate-800 dark:bg-transparent">
                        @foreach(\App\Models\Maintenance::where('status', 'scheduled')->with('truck')->orderBy('maintenance_date')->take(5)->get() as $maintenance)
                            <tr class="transition hover:bg-slate-50/80 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ $maintenance->truck->plate_number }}</td>
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-300">{{ $maintenance->maintenance_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-300">{{ $maintenance->maintenance_type }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-end">
                <a href="{{ route('fleet.maintenance.index') }}" class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                    Ver todos
                    <span>&rarr;</span>
                </a>
            </div>
        </article>
    </section>
</x-layouts.app>
