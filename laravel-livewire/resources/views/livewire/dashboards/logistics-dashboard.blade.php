<div class="space-y-6">
 <section class="grid gap-6 xl:grid-cols-[2fr_1fr]">
 <article class="surface-card">
 <header class="flex items-center justify-between border-b border-slate-200 px-6 py-5 ">
 <div>
 <h1 class="text-2xl font-semibold text-slate-900 ">{{ __('Seguimiento logístico') }}</h1>
 <p class="mt-1 text-sm text-slate-600 ">{{ __('Estado consolidado de pedidos, ventanas de entrega y desempeño operativo.') }}</p>
 </div>
 </header>
 <div class="grid gap-4 p-6 sm:grid-cols-4">
 <x-dashboard.stat :label="__('Órdenes totales')" :value="$ordersSummary['total']" icon="package" />
 <x-dashboard.stat :label="__('En ruta')" :value="$ordersSummary['en_route']" icon="navigation" />
 <x-dashboard.stat :label="__('Pendientes')" :value="$ordersSummary['pending']" icon="calendar" />
 <x-dashboard.stat :label="__('Entregadas')" :value="$ordersSummary['delivered']" icon="check" />
 </div>
 </article>

 <article class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Indicadores clave') }}</h2>
 </header>
 <div class="space-y-4 p-6 text-sm text-slate-600 ">
 <div class="flex items-center justify-between">
 <span>{{ __('Disponibilidad de flota') }}</span>
 <span class="text-base font-semibold text-emerald-500 ">{{ number_format($availableTrucks) }}</span>
 </div>
 <div class="flex items-center justify-between">
 <span>{{ __('Entregas a tiempo') }}</span>
 <span class="text-base font-semibold">{{ $onTimeRate !== null ? $onTimeRate.'%' : '—' }}</span>
 </div>
 <div class="flex items-center justify-between">
 <span>{{ __('Costo promedio por envío') }}</span>
 <span class="text-base font-semibold">{{ $averageCost ? \App\Support\Formatters\MoneyFormatter::pen($averageCost) : '—' }}</span>
 </div>
 <div class="flex items-center justify-between">
 <span>{{ __('Incidencias activas') }}</span>
 <span class="text-base font-semibold text-amber-500">{{ $activeIncidents }}</span>
 </div>
 <div class="flex items-center justify-between">
 <span>{{ __('Reservas de inventario confirmadas') }}</span>
 <span class="text-base font-semibold text-sky-500">{{ $openReservations }}</span>
 </div>
 </div>
 </article>
 </section>

 <section class="grid gap-6 lg:grid-cols-2">
 <article class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Próximas asignaciones') }}</h2>
 </header>
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-slate-200 text-sm ">
 <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 ">
 <tr>
 <th class="px-4 py-3">{{ __('Camión') }}</th>
 <th class="px-4 py-3">{{ __('Chofer') }}</th>
 <th class="px-4 py-3">{{ __('Orden') }}</th>
 <th class="px-4 py-3">{{ __('Inicio') }}</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-slate-100 bg-white ">
 @forelse ($upcomingAssignments as $assignment)
 <tr class="transition hover:bg-slate-50 ">
 <td class="px-4 py-3 font-medium text-slate-900 ">{{ optional($assignment->truck)->plate_number ?? '—' }}</td>
 <td class="px-4 py-3 text-slate-600 ">{{ optional($assignment->driver)->full_name ?? optional($assignment->driver)->name ?? '—' }}</td>
 <td class="px-4 py-3 text-slate-600 ">{{ optional($assignment->order)->reference ?? '—' }}</td>
 <td class="px-4 py-3 text-slate-600 ">{{ optional($assignment->start_date)?->format('d/m/Y H:i') ?? '—' }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 ">{{ __('Sin asignaciones próximas.') }}</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </article>

 <article class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Órdenes recientes') }}</h2>
 </header>
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-slate-200 text-sm ">
 <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 ">
 <tr>
 <th class="px-4 py-3">{{ __('Referencia') }}</th>
 <th class="px-4 py-3">{{ __('Cliente') }}</th>
 <th class="px-4 py-3">{{ __('Estado') }}</th>
 <th class="px-4 py-3">{{ __('Recolección') }}</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-slate-100 bg-white ">
 @forelse ($recentOrders as $order)
 <tr class="transition hover:bg-slate-50 ">
 <td class="px-4 py-3 font-medium text-slate-900 ">{{ $order->reference ?? '—' }}</td>
 <td class="px-4 py-3 text-slate-600 ">{{ optional($order->client)->business_name ?? optional($order->client)->contact_name ?? '—' }}</td>
 <td class="px-4 py-3">
 @php
 $statusMap = [
 'pending' => 'Pendiente',
 'en_route' => 'En ruta',
 'delivered' => 'Entregado',
 'cancelled' => 'Cancelado',
 ];
 @endphp

 <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700 ">
 {{ $statusMap[$order->status ?? 'pending'] ?? 'Desconocido' }}
 </span>
 </td>


 <td class="px-4 py-3 text-slate-600 ">{{ optional($order->pickup_date)?->format('d/m/Y') ?? '—' }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 ">{{ __('Registra órdenes para visualizar actividad reciente.') }}</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </article>
 </section>

 <section class="grid gap-6 lg:grid-cols-2">
 <article class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Incidencias en ruta') }}</h2>
 </header>
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-slate-200 text-sm ">
 <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 ">
 <tr>
 <th class="px-4 py-3">{{ __('Tipo') }}</th>
 <th class="px-4 py-3">{{ __('Severidad') }}</th>
 <th class="px-4 py-3">{{ __('Asignación') }}</th>
 <th class="px-4 py-3">{{ __('Reportado') }}</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-slate-100 bg-white ">
 @forelse ($recentIncidents as $incident)
 <tr class="transition hover:bg-slate-50 ">
 <td class="px-4 py-3 font-medium text-slate-900 ">{{ __($incident->type) }}</td>
 <td class="px-4 py-3">
 <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 ">{{ __($incident->severity) }}</span>
 </td>
 <td class="px-4 py-3 text-slate-600 ">
 {{ optional($incident->assignment?->order)->reference ?? '—' }} · {{ optional($incident->assignment?->truck)->plate_number ?? '—' }}
 </td>
 <td class="px-4 py-3 text-slate-600 ">{{ optional($incident->reported_at)?->format('d/m/Y H:i') ?? '—' }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 ">{{ __('No hay incidencias registradas recientemente.') }}</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </article>

 <article class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Historial de rutas') }}</h2>
 </header>
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-slate-200 text-sm ">
 <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 ">
 <tr>
 <th class="px-4 py-3">{{ __('Pedido') }}</th>
 <th class="px-4 py-3">{{ __('Planificador') }}</th>
 <th class="px-4 py-3">{{ __('Distancia (km)') }}</th>
 <th class="px-4 py-3">{{ __('Actualizado') }}</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-slate-100 bg-white ">
 @forelse ($routeHistory as $plan)
 <tr class="transition hover:bg-slate-50 ">
 <td class="px-4 py-3 font-medium text-slate-900 ">{{ optional($plan->order)->reference ?? '—' }}</td>
 <td class="px-4 py-3 text-slate-600 ">{{ $plan->planner ?? __('Sistema') }}</td>
 <td class="px-4 py-3 text-slate-600 ">{{ data_get($plan->route_data, 'distance_km') ?? '—' }}</td>
 <td class="px-4 py-3 text-slate-600 ">{{ optional($plan->updated_at)?->diffForHumans() }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 ">{{ __('No se han registrado rutas aún.') }}</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </article>
 </section>

 <section class="grid gap-6 lg:grid-cols-[3fr_2fr]">
 <article class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Seguimiento en tiempo real') }}</h2>
 </header>
 <div class="p-4">
 <livewire:logistics.live-tracking-board :latestTracking="$latestTracking" />
 </div>
 </article>

 <article class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Últimas posiciones reportadas') }}</h2>
 </header>
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-slate-200 text-sm ">
 <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 ">
 <tr>
 <th class="px-4 py-3">{{ __('Vehículo') }}</th>
 <th class="px-4 py-3">{{ __('Pedido') }}</th>
 <th class="px-4 py-3">{{ __('Estado') }}</th>
 <th class="px-4 py-3">{{ __('Reportado') }}</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-slate-100 bg-white ">
 @forelse ($latestTracking as $tracking)
 <tr class="transition hover:bg-slate-50 ">
 <td class="px-4 py-3 font-medium text-slate-900 ">{{ optional($tracking->truck)->plate_number ?? '—' }}</td>
 <td class="px-4 py-3 text-slate-600 ">{{ optional($tracking->assignment?->order)->reference ?? '—' }}</td>
 <td class="px-4 py-3 text-slate-600 ">{{ __($tracking->status) }}</td>
 <td class="px-4 py-3 text-slate-600 ">{{ optional($tracking->reported_at)?->format('d/m/Y H:i') ?? '—' }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 ">{{ __('Sin reportes recientes de GPS.') }}</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </article>
 </section>
</div>

