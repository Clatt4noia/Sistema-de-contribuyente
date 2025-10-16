<x-layouts.app :title="__('Dashboard')">
 <section class="grid gap-6 xl:grid-cols-[3fr,2fr]">
 <article class="relative overflow-hidden rounded-3xl border border-[color:var(--color-primary-border)] bg-gradient-to-br from-[color:var(--color-elevated)] via-[color:var(--color-primary-muted)] to-[color:var(--color-primary-100)] p-8 text-slate-800 shadow-lg transition-colors duration-300 ">
 <div class="pointer-events-none absolute inset-0 -z-10 opacity-40 mix-blend-soft-light ">
 <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-[color:var(--color-primary-200)] opacity-40 blur-3xl "></div>
 <div class="absolute -bottom-24 -left-10 h-64 w-64 rounded-full bg-[color:var(--color-primary-300)] opacity-30 blur-3xl "></div>
 </div>

 <div class="flex flex-wrap items-start justify-between gap-6">
 <div class="max-w-xl space-y-3">
 <span class="surface-pill text-accent">
 <span class="h-1.5 w-1.5 rounded-full bg-[color:var(--color-primary)]"></span>
 Flujo activo
 </span>
 <h2 class="text-3xl font-semibold tracking-tight text-slate-900 ">Gestión integral en un solo lugar</h2>
 <p class="text-sm leading-relaxed text-slate-600 ">
 Supervisa camiones, conductores, pedidos y finanzas con información en vivo. Ajusta tus operaciones y mantén a todo el equipo alineado en segundos.
 </p>
 </div>

 <dl class="grid gap-3 text-right text-sm text-slate-600 sm:grid-cols-2">
 <div class="surface-muted px-4 py-3">
 <dt class="text-xs uppercase tracking-wide text-accent-soft ">Camiones activos</dt>
 <dd class="text-2xl font-semibold text-slate-900 ">{{ \App\Models\Truck::where('status', 'active')->count() }}</dd>
 </div>
 <div class="surface-muted px-4 py-3">
 <dt class="text-xs uppercase tracking-wide text-accent-soft ">Conductores</dt>
 <dd class="text-2xl font-semibold text-slate-900 ">{{ \App\Models\Driver::count() }}</dd>
 </div>
 </dl>
 </div>

 <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
 <a href="{{ route('fleet.trucks.index') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-accent transition hover:-translate-y-0.5">
 <span>Camiones</span>
 <span class="text-accent-soft transition group-hover:translate-x-0.5 ">&rarr;</span>
 </a>
 <a href="{{ route('fleet.drivers.index') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-accent transition hover:-translate-y-0.5">
 <span>Conductores</span>
 <span class="text-accent-soft transition group-hover:translate-x-0.5 ">&rarr;</span>
 </a>
 <a href="{{ route('fleet.assignments.index') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-accent transition hover:-translate-y-0.5">
 <span>Asignaciones</span>
 <span class="text-accent-soft transition group-hover:translate-x-0.5 ">&rarr;</span>
 </a>
 <a href="{{ route('fleet.report') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-accent transition hover:-translate-y-0.5">
 <span>Reportes</span>
 <span class="text-accent-soft transition group-hover:translate-x-0.5 ">&rarr;</span>
 </a>
 <a href="{{ route('fleet.maintenance.index') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-accent transition hover:-translate-y-0.5">
 <span>Mantenimientos</span>
 <span class="text-accent-soft transition group-hover:translate-x-0.5 ">&rarr;</span>
 </a>
 </div>
 </article>

 <article class="surface-card p-6">
 <h3 class="text-xl font-semibold text-slate-900 ">Operaciones y Finanzas</h3>
 <p class="mt-2 text-sm leading-relaxed text-slate-600 ">
 Accesos directos a los registros clave del día a día. Revisa pedidos, clientes y facturación sin perderte entre pantallas.
 </p>
 <div class="mt-6 grid gap-3">
 <a href="{{ route('orders.index') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-[color:var(--color-primary-border)] hover:[background-color:var(--color-elevated)] ">
 <span>Pedidos</span>
 <span class="text-accent-soft transition group-hover:translate-x-0.5 ">&rarr;</span>
 </a>
 <a href="{{ route('clients.index') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-[color:var(--color-primary-border)] hover:[background-color:var(--color-elevated)] ">
 <span>Clientes</span>
 <span class="text-accent-soft transition group-hover:translate-x-0.5 ">&rarr;</span>
 </a>
 <a href="{{ route('billing.invoices.index') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-[color:var(--color-primary-border)] hover:[background-color:var(--color-elevated)] ">
 <span>Facturas</span>
 <span class="text-accent-soft transition group-hover:translate-x-0.5 ">&rarr;</span>
 </a>
 <a href="{{ route('billing.payments.index') }}" class="group surface-muted flex transform items-center justify-between px-4 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-[color:var(--color-primary-border)] hover:[background-color:var(--color-elevated)] ">
 <span>Pagos</span>
 <span class="text-accent-soft transition group-hover:translate-x-0.5 ">&rarr;</span>
 </a>
 </div>
 <div class="mt-6 flex items-center gap-3 text-xs font-medium uppercase tracking-wide text-slate-400 ">
 <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400 "></span>
 Información actualizada al día de hoy.
 </div>
 </article>
 </section>

 <section class="grid gap-6 lg:grid-cols-3">
 <article class="surface-card p-6">
 <header class="space-y-2">
 <span class="surface-pill text-xs font-semibold uppercase tracking-wide text-accent ">1. Gestión de Pedidos y Rutas</span>
 <h3 class="text-xl font-semibold text-slate-900 ">Control logístico en tiempo real</h3>
 <p class="text-sm text-slate-600 ">Organiza cada pedido, asigna recursos y visualiza rutas optimizadas con estados claros.</p>
 </header>
 <ul class="mt-4 space-y-3 text-sm text-slate-600 ">
 <li class="flex items-start gap-2">
 <span class="mt-1 h-1.5 w-1.5 rounded-full bg-accent-soft0"></span>
 Registro y seguimiento completo de pedidos de transporte.
 </li>
 <li class="flex items-start gap-2">
 <span class="mt-1 h-1.5 w-1.5 rounded-full bg-accent-soft0"></span>
 Asignación inmediata de camiones y choferes a cada pedido.
 </li>
 <li class="flex items-start gap-2">
 <span class="mt-1 h-1.5 w-1.5 rounded-full bg-accent-soft0"></span>
 Planificación y optimización de rutas con soporte de mapas.
 </li>
 <li class="flex items-start gap-2">
 <span class="mt-1 h-1.5 w-1.5 rounded-full bg-accent-soft0"></span>
 Estados del pedido: pendiente, en ruta, entregado o cancelado.
 </li>
 </ul>
 <div class="mt-6">
 <a href="{{ route('orders.index') }}" class="surface-pill transform text-accent transition hover:-translate-y-0.5 hover:border-[color:var(--color-primary-border)] hover:[background-color:var(--color-elevated)] ">
 Abrir módulo
 <span>&rarr;</span>
 </a>
 </div>
 </article>

 <article class="surface-card p-6">
 <header class="space-y-2">
 <span class="surface-pill text-xs font-semibold uppercase tracking-wide text-accent ">2. Gestión de Flota y Choferes</span>
 <h3 class="text-xl font-semibold text-slate-900 ">Disponibilidad y mantenimiento asegurados</h3>
 <p class="text-sm text-slate-600 ">Visualiza la salud de cada vehículo y el desempeño de tus conductores sin perder detalle.</p>
 </header>
 <ul class="mt-4 space-y-3 text-sm text-slate-600 ">
 <li class="flex items-start gap-2">
 <span class="mt-1 h-1.5 w-1.5 rounded-full bg-[color:var(--color-primary)]"></span>
 Registro técnico de camiones con historial de mantenimiento.
 </li>
 <li class="flex items-start gap-2">
 <span class="mt-1 h-1.5 w-1.5 rounded-full bg-[color:var(--color-primary)]"></span>
 Control de horarios, licencias y evaluaciones de choferes.
 </li>
 <li class="flex items-start gap-2">
 <span class="mt-1 h-1.5 w-1.5 rounded-full bg-[color:var(--color-primary)]"></span>
 Seguimiento de reparaciones y notificaciones preventivas.
 </li>
 <li class="flex items-start gap-2">
 <span class="mt-1 h-1.5 w-1.5 rounded-full bg-[color:var(--color-primary)]"></span>
 Control de disponibilidad y asignación en un tablero único.
 </li>
 </ul>
 <div class="mt-6">
 <a href="{{ route('fleet.trucks.index') }}" class="surface-pill transform text-accent transition hover:-translate-y-0.5 hover:border-[color:var(--color-primary-border)] hover:[background-color:var(--color-elevated)] ">
 Abrir módulo
 <span>&rarr;</span>
 </a>
 </div>
 </article>

 <article class="surface-card p-6">
 <header class="space-y-2">
 <span class="surface-pill text-xs font-semibold uppercase tracking-wide text-success ">3. Facturación y Pagos</span>
 <h3 class="text-xl font-semibold text-slate-900 ">Ingresos bajo control</h3>
 <p class="text-sm text-slate-600 ">Genera facturas, registra pagos y analiza resultados financieros con claridad.</p>
 </header>
 <ul class="mt-4 space-y-3 text-sm text-slate-600 ">
 <li class="flex items-start gap-2">
 <span class="mt-1 h-1.5 w-1.5 rounded-full bg-success-soft0"></span>
 Emisión automática de facturas por servicios realizados.
 </li>
 <li class="flex items-start gap-2">
 <span class="mt-1 h-1.5 w-1.5 rounded-full bg-success-soft0"></span>
 Control de pagos recibidos y pendientes por cliente.
 </li>
 <li class="flex items-start gap-2">
 <span class="mt-1 h-1.5 w-1.5 rounded-full bg-success-soft0"></span>
 Reportes financieros para evaluar ingresos y gastos.
 </li>
 <li class="flex items-start gap-2">
 <span class="mt-1 h-1.5 w-1.5 rounded-full bg-success-soft0"></span>
 Gestión completa de clientes con datos fiscales y condiciones.
 </li>
 </ul>
 <div class="mt-6">
 <a href="{{ route('billing.invoices.index') }}" class="surface-pill transform text-success transition hover:-translate-y-0.5 hover:border-[color:var(--color-success-200)] hover:[background-color:var(--color-elevated)] ">
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
 <h3 class="text-lg font-semibold text-slate-900 ">Camiones disponibles</h3>
 <p class="mt-1 text-sm text-slate-600 ">Últimas unidades listas para despacho.</p>
 </div>
 <span class="surface-pill border-success-soft bg-success-soft text-xs font-semibold text-success-strong ">Stock</span>
 </div>
        <div class="mt-4 overflow-x-auto">
          <table class="table table-md">
            <thead>
              <tr class="table-row">
                <th class="table-header">Placa</th>
                <th class="table-header">Modelo</th>
                <th class="table-header">Estado</th>
              </tr>
            </thead>
            <tbody>
              @foreach(\App\Models\Truck::where('status', 'available')->orderBy('plate_number')->take(5)->get() as $truck)
                <tr class="table-row table-row-hover">
                  <td class="table-cell text-sm font-medium text-slate-900 ">{{ $truck->plate_number }}</td>
                  <td class="table-cell text-sm text-slate-600 ">{{ $truck->brand }} {{ $truck->model }}</td>
                  <td class="table-cell">
                    <span class="inline-flex items-center rounded-full bg-success-soft px-3 py-1 text-xs font-semibold text-success-strong ">
                      Disponible
                    </span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
 <div class="mt-4 flex justify-end">
 <a href="{{ route('fleet.trucks.index') }}" class="surface-pill transform border-accent-soft text-accent transition hover:-translate-y-0.5 hover:border-[color:var(--color-primary-border)] hover:[background-color:var(--color-elevated)] ">
 Ver todos
 <span>&rarr;</span>
 </a>
 </div>
 </article>

 <article class="surface-card p-6">
 <div class="flex flex-wrap items-start justify-between gap-4">
 <div>
 <h3 class="text-lg font-semibold text-slate-900 ">Mantenimientos programados</h3>
 <p class="mt-1 text-sm text-slate-600 ">Próximas intervenciones agendadas.</p>
 </div>
 <span class="surface-pill border-warning-soft bg-warning-soft text-xs font-semibold text-warning ">Agenda</span>
 </div>
        <div class="mt-4 overflow-x-auto">
          <table class="table table-md">
            <thead>
              <tr class="table-row">
                <th class="table-header">Vehículo</th>
                <th class="table-header">Fecha</th>
                <th class="table-header">Tipo</th>
              </tr>
            </thead>
            <tbody>
              @foreach(\App\Models\Maintenance::where('status', 'scheduled')->with('truck')->orderBy('maintenance_date')->take(5)->get() as $maintenance)
                <tr class="table-row table-row-hover">
                  <td class="table-cell text-sm font-medium text-slate-900 ">{{ $maintenance->truck->plate_number }}</td>
                  <td class="table-cell text-sm text-slate-600 ">{{ $maintenance->maintenance_date->format('d/m/Y') }}</td>
                  <td class="table-cell text-sm text-slate-600 ">{{ $maintenance->maintenance_type }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
 <div class="mt-4 flex justify-end">
 <a href="{{ route('fleet.maintenance.index') }}" class="surface-pill transform border-accent-soft text-accent transition hover:-translate-y-0.5 hover:border-[color:var(--color-primary-border)] hover:[background-color:var(--color-elevated)] ">
 Ver todos
 <span>&rarr;</span>
 </a>
 </div>
 </article>
 </section>
</x-layouts.app>
