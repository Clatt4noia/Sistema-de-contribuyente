<x-layouts.app :title="__('Dashboard')">
    <section class="grid gap-6 xl:grid-cols-[3fr,2fr]">
        <article class="relative overflow-hidden rounded-3xl border border-cyan-100/70 bg-gradient-to-br from-white via-cyan-50 to-sky-100 p-8 text-slate-800 shadow-lg transition-colors duration-300 dark:border-indigo-500/30 dark:from-slate-950/90 dark:via-slate-950/70 dark:to-slate-950/60 dark:text-slate-100">
            <div class="pointer-events-none absolute inset-0 -z-10 opacity-40 mix-blend-soft-light dark:opacity-30">
                <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-cyan-200/40 blur-3xl dark:bg-indigo-500/20"></div>
                <div class="absolute -bottom-24 -left-10 h-64 w-64 rounded-full bg-sky-300/30 blur-3xl dark:bg-cyan-500/20"></div>
            </div>

            <div class="flex flex-wrap items-start justify-between gap-6">
                <div class="max-w-xl space-y-3">
                    <span class="surface-pill text-xs uppercase tracking-wide text-cyan-600 dark:text-cyan-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-cyan-500"></span>
                        Flujo activo
                    </span>
                    <h2 class="text-3xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">Gestión integral en un solo lugar</h2>
                    <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        Supervisa camiones, conductores, pedidos y finanzas con información en vivo. Ajusta tus operaciones y mantén a todo el equipo alineado en segundos.
                    </p>
                </div>

                <dl class="grid gap-3 text-right text-sm text-slate-600 dark:text-slate-300 sm:grid-cols-2">
                    <div class="surface-muted px-4 py-3">
                        <dt class="text-xs uppercase tracking-wide text-cyan-500 dark:text-cyan-300">Camiones activos</dt>
                        <dd class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ \App\Models\Truck::where('status', 'active')->count() }}</dd>
                    </div>
                    <div class="surface-muted px-4 py-3">
                        <dt class="text-xs uppercase tracking-wide text-cyan-500 dark:text-cyan-300">Conductores</dt>
                        <dd class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ \App\Models\Driver::count() }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('fleet.trucks.index') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-cyan-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white dark:text-cyan-200 dark:hover:border-indigo-400 dark:hover:bg-slate-900">
                    <span>Camiones</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5 dark:text-cyan-300">&rarr;</span>
                </a>
                <a href="{{ route('fleet.drivers.index') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-cyan-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white dark:text-cyan-200 dark:hover:border-indigo-400 dark:hover:bg-slate-900">
                    <span>Conductores</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5 dark:text-cyan-300">&rarr;</span>
                </a>
                <a href="{{ route('fleet.assignments.index') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-cyan-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white dark:text-cyan-200 dark:hover:border-indigo-400 dark:hover:bg-slate-900">
                    <span>Asignaciones</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5 dark:text-cyan-300">&rarr;</span>
                </a>
                <a href="{{ route('fleet.report') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-cyan-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white dark:text-cyan-200 dark:hover:border-indigo-400 dark:hover:bg-slate-900">
                    <span>Reportes</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5 dark:text-cyan-300">&rarr;</span>
                </a>
                <a href="{{ route('fleet.maintenance.index') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-cyan-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white dark:text-cyan-200 dark:hover:border-indigo-400 dark:hover:bg-slate-900">
                    <span>Mantenimientos</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5 dark:text-cyan-300">&rarr;</span>
                </a>
            </div>
        </article>

        <article class="surface-card p-6">
            <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Operaciones y Finanzas</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                Accesos directos a los registros clave del día a día. Revisa pedidos, clientes y facturación sin perderte entre pantallas.
            </p>
            <div class="mt-6 grid gap-3">
                <a href="{{ route('orders.index') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white dark:text-slate-200 dark:hover:border-indigo-400 dark:hover:bg-slate-900">
                    <span>Pedidos</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5 dark:text-cyan-300">&rarr;</span>
                </a>
                <a href="{{ route('clients.index') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white dark:text-slate-200 dark:hover:border-indigo-400 dark:hover:bg-slate-900">
                    <span>Clientes</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5 dark:text-cyan-300">&rarr;</span>
                </a>
                <a href="{{ route('billing.invoices.index') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white dark:text-slate-200 dark:hover:border-indigo-400 dark:hover:bg-slate-900">
                    <span>Facturas</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5 dark:text-cyan-300">&rarr;</span>
                </a>
                <a href="{{ route('billing.payments.index') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:bg-white dark:text-slate-200 dark:hover:border-indigo-400 dark:hover:bg-slate-900">
                    <span>Pagos</span>
                    <span class="text-cyan-500 transition group-hover:translate-x-0.5 dark:text-cyan-300">&rarr;</span>
                </a>
            </div>
            <div class="mt-6 flex items-center gap-3 text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400 dark:bg-emerald-500"></span>
                Información actualizada al día de hoy.
            </div>
        </article>
    </section>

    <section class="grid gap-6 lg:grid-cols-3">
        <article class="surface-card p-6">
            <header class="space-y-2">
                <span class="surface-pill text-xs font-semibold uppercase tracking-wide text-sky-600 dark:text-sky-300">1. Gestión de Pedidos y Rutas</span>
                <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Control logístico en tiempo real</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300">Organiza cada pedido, asigna recursos y visualiza rutas optimizadas con estados claros.</p>
            </header>
            <ul class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                <li class="flex items-start gap-2">
                    <span class="mt-1 h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                    Registro y seguimiento completo de pedidos de transporte.
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-1 h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                    Asignación inmediata de camiones y choferes a cada pedido.
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-1 h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                    Planificación y optimización de rutas con soporte de mapas.
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-1 h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                    Estados del pedido: pendiente, en ruta, entregado o cancelado.
                </li>
            </ul>
            <div class="mt-6">
                <a href="{{ route('orders.index') }}" class="surface-pill transform text-sky-600 transition hover:-translate-y-0.5 hover:border-sky-300 hover:bg-white dark:text-sky-200 dark:hover:border-indigo-400 dark:hover:bg-slate-900">
                    Abrir módulo
                    <span>&rarr;</span>
                </a>
            </div>
        </article>

        <article class="surface-card p-6">
            <header class="space-y-2">
                <span class="surface-pill text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-300">2. Gestión de Flota y Choferes</span>
                <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Disponibilidad y mantenimiento asegurados</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300">Visualiza la salud de cada vehículo y el desempeño de tus conductores sin perder detalle.</p>
            </header>
            <ul class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                <li class="flex items-start gap-2">
                    <span class="mt-1 h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                    Registro técnico de camiones con historial de mantenimiento.
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-1 h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                    Control de horarios, licencias y evaluaciones de choferes.
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-1 h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                    Seguimiento de reparaciones y notificaciones preventivas.
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-1 h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                    Control de disponibilidad y asignación en un tablero único.
                </li>
            </ul>
            <div class="mt-6">
                <a href="{{ route('fleet.trucks.index') }}" class="surface-pill transform text-indigo-600 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-white dark:text-indigo-200 dark:hover:border-indigo-400 dark:hover:bg-slate-900">
                    Abrir módulo
                    <span>&rarr;</span>
                </a>
            </div>
        </article>

        <article class="surface-card p-6">
            <header class="space-y-2">
                <span class="surface-pill text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">3. Facturación y Pagos</span>
                <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Ingresos bajo control</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300">Genera facturas, registra pagos y analiza resultados financieros con claridad.</p>
            </header>
            <ul class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                <li class="flex items-start gap-2">
                    <span class="mt-1 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Emisión automática de facturas por servicios realizados.
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-1 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Control de pagos recibidos y pendientes por cliente.
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-1 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Reportes financieros para evaluar ingresos y gastos.
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-1 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Gestión completa de clientes con datos fiscales y condiciones.
                </li>
            </ul>
            <div class="mt-6">
                <a href="{{ route('billing.invoices.index') }}" class="surface-pill transform text-emerald-600 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-white dark:text-emerald-200 dark:hover:border-emerald-400 dark:hover:bg-slate-900">
                    Abrir módulo
                    <span>&rarr;</span>
                </a>
            </div>
        </article>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="surface-card p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Camiones disponibles</h3>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Últimas unidades listas para despacho.</p>
                </div>
                <span class="surface-pill border-emerald-200/70 bg-emerald-50/80 text-xs font-semibold text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-300">Stock</span>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Placa</th>
                            <th class="px-4 py-3">Modelo</th>
                            <th class="px-4 py-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                        @foreach(\App\Models\Truck::where('status', 'available')->orderBy('plate_number')->take(5)->get() as $truck)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $truck->plate_number }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $truck->brand }} {{ $truck->model }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                                        Disponible
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-end">
                <a href="{{ route('fleet.trucks.index') }}" class="surface-pill transform border-cyan-200 text-cyan-700 transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-white dark:border-indigo-500/50 dark:text-cyan-200 dark:hover:border-indigo-400 dark:hover:bg-slate-900">
                    Ver todos
                    <span>&rarr;</span>
                </a>
            </div>
        </article>

        <article class="surface-card p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Mantenimientos programados</h3>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Próximas intervenciones agendadas.</p>
                </div>
                <span class="surface-pill border-amber-200/70 bg-amber-50/80 text-xs font-semibold text-amber-700 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-300">Agenda</span>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Vehículo</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Tipo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-950/50">
                        @foreach(\App\Models\Maintenance::where('status', 'scheduled')->with('truck')->orderBy('maintenance_date')->take(5)->get() as $maintenance)
                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-900/70">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $maintenance->truck->plate_number }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $maintenance->maintenance_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $maintenance->maintenance_type }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-end">
                <a href="{{ route('fleet.maintenance.index') }}" class="surface-pill transform border-cyan-200 text-cyan-700 transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-white dark:border-indigo-500/50 dark:text-cyan-200 dark:hover:border-indigo-400 dark:hover:bg-slate-900">
                    Ver todos
                    <span>&rarr;</span>
                </a>
            </div>
        </article>
    </section>
</x-layouts.app>
