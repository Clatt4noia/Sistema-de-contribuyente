<div class="space-y-6">
 <section class="grid gap-6 xl:grid-cols-[2fr_1fr]">
 <article class="surface-card">
 <header class="flex items-center justify-between border-b border-token px-6 py-5 ">
 <div>
 <h1 class="text-2xl font-semibold text-token ">{{ __('Seguimiento logístico') }}</h1>
 <p class="mt-1 text-sm text-token ">{{ __('Estado consolidado de Ordenes, ventanas de entrega y desempeño operativo.') }}</p>
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
 <header class="border-b border-token px-6 py-5 ">
 <h2 class="text-lg font-semibold text-token ">{{ __('Indicadores clave') }}</h2>
 </header>
 <div class="space-y-4 p-6 text-sm text-token ">
 <div class="flex items-center justify-between">
 <span>{{ __('Disponibilidad de flota') }}</span>
 <span class="text-base font-semibold text-success ">{{ number_format($availableTrucks) }}</span>
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
 <span class="text-base font-semibold text-warning">{{ $activeIncidents }}</span>
 </div>
 <div class="flex items-center justify-between">
 <span>{{ __('Reservas de inventario confirmadas') }}</span>
 <span class="text-base font-semibold text-accent-soft">{{ $openReservations }}</span>
 </div>
 </div>
 </article>
 </section>


 <section class="grid gap-6 lg:grid-cols-2">
 <article class="surface-card">
 <header class="border-b border-token px-6 py-5 ">
 <h2 class="text-lg font-semibold text-token ">{{ __('Próximas asignaciones') }}</h2>
 </header>
        <div class="overflow-x-auto">
          <table class="table table-md">
            <thead>
              <tr class="table-row">
                <th class="table-header">{{ __('Camión') }}</th>
                <th class="table-header">{{ __('Chofer') }}</th>
                <th class="table-header">{{ __('Orden') }}</th>
                <th class="table-header">{{ __('Inicio') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($upcomingAssignments as $assignment)
                <tr class="table-row table-row-hover">
                  <td class="table-cell text-sm font-medium text-token ">{{ optional($assignment->truck)->plate_number ?? '—' }}</td>
                  <td class="table-cell text-sm text-token ">{{ optional($assignment->driver)->full_name ?? optional($assignment->driver)->name ?? '—' }}</td>
                  <td class="table-cell text-sm text-token ">{{ optional($assignment->order)->reference ?? '—' }}</td>
                  <td class="table-cell text-sm text-token ">{{ optional($assignment->start_date)?->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>
              @empty
                <tr class="table-row">
                  <td colspan="4" class="table-empty">{{ __('Sin asignaciones próximas.') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
 </article>

 <article class="surface-card">
 <header class="border-b border-token px-6 py-5 ">
 <h2 class="text-lg font-semibold text-token ">{{ __('Órdenes recientes') }}</h2>
 </header>
        <div class="overflow-x-auto">
          <table class="table table-md">
            <thead>
              <tr class="table-row">
                <th class="table-header">{{ __('Referencia') }}</th>
                <th class="table-header">{{ __('Cliente') }}</th>
                <th class="table-header">{{ __('Estado') }}</th>
                <th class="table-header">{{ __('Recolección') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($recentOrders as $order)
                <tr class="table-row table-row-hover">
                  <td class="table-cell text-sm font-medium text-token ">{{ $order->reference ?? '—' }}</td>
                  <td class="table-cell text-sm text-token ">{{ optional($order->client)->business_name ?? optional($order->client)->contact_name ?? '—' }}</td>
                  <td class="table-cell">
                    @php
                      $statusMap = [
                        'pending' => 'Pendiente',
                        'en_route' => 'En ruta',
                        'delivered' => 'Entregado',
                        'cancelled' => 'Cancelado',
                      ];
                    @endphp

                    <span class="rounded-full bg-accent-soft px-3 py-1 text-xs font-semibold text-accent ">
                      {{ $statusMap[$order->status ?? 'pending'] ?? 'Desconocido' }}
                    </span>
                  </td>
                  <td class="table-cell text-sm text-token ">{{ optional($order->pickup_date)?->format('d/m/Y') ?? '—' }}</td>
                </tr>
              @empty
                <tr class="table-row">
                  <td colspan="4" class="table-empty">{{ __('Registra órdenes para visualizar actividad reciente.') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
 </article>
 </section>

 <section class="grid gap-6 lg:grid-cols-2">
 <article class="surface-card">
 <header class="border-b border-token px-6 py-5 ">
 <h2 class="text-lg font-semibold text-token ">{{ __('Incidencias en ruta') }}</h2>
 </header>
        <div class="overflow-x-auto">
          <table class="table table-md">
            <thead>
              <tr class="table-row">
                <th class="table-header">{{ __('Tipo') }}</th>
                <th class="table-header">{{ __('Severidad') }}</th>
                <th class="table-header">{{ __('Asignación') }}</th>
                <th class="table-header">{{ __('Reportado') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($recentIncidents as $incident)
                <tr class="table-row table-row-hover">
                  <td class="table-cell text-sm font-medium text-token ">{{ __($incident->type) }}</td>
                  <td class="table-cell">
                    <span class="rounded-full bg-warning-soft px-3 py-1 text-xs font-semibold text-warning ">{{ __($incident->severity) }}</span>
                  </td>
                  <td class="table-cell text-sm text-token ">
                    {{ optional($incident->assignment?->order)->reference ?? '—' }} · {{ optional($incident->assignment?->truck)->plate_number ?? '—' }}
                  </td>
                  <td class="table-cell text-sm text-token ">{{ optional($incident->reported_at)?->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>
              @empty
                <tr class="table-row">
                  <td colspan="4" class="table-empty">{{ __('No hay incidencias registradas recientemente.') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
 </article>

 <article class="surface-card">
 <header class="border-b border-token px-6 py-5 ">
 <h2 class="text-lg font-semibold text-token ">{{ __('Historial de rutas') }}</h2>
 </header>
        <div class="overflow-x-auto">
          <table class="table table-md">
            <thead>
              <tr class="table-row">
                <th class="table-header">{{ __('Orden') }}</th>
                <th class="table-header">{{ __('Planificador') }}</th>
                <th class="table-header">{{ __('Distancia (km)') }}</th>
                <th class="table-header">{{ __('Actualizado') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($routeHistory as $plan)
                <tr class="table-row table-row-hover">
                  <td class="table-cell text-sm font-medium text-token ">{{ optional($plan->order)->reference ?? '—' }}</td>
                  <td class="table-cell text-sm text-token ">{{ $plan->planner ?? __('Sistema') }}</td>
                  <td class="table-cell text-sm text-token ">{{ data_get($plan->route_data, 'distance_km') ?? '—' }}</td>
                  <td class="table-cell text-sm text-token ">{{ optional($plan->updated_at)?->diffForHumans() }}</td>
                </tr>
              @empty
                <tr class="table-row">
                  <td colspan="4" class="table-empty">{{ __('No se han registrado rutas aún.') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
 </article>
 </section>

 <section class="grid gap-6 lg:grid-cols-[3fr_2fr]">
 <article class="surface-card">
 <header class="border-b border-token px-6 py-5 ">
 <h2 class="text-lg font-semibold text-token ">{{ __('Seguimiento en tiempo real') }}</h2>
 </header>
 <div class="p-4">
 <livewire:logistics.live-tracking-board :latestTracking="$latestTracking" />
 </div>
 </article>

 <article class="surface-card">
 <header class="border-b border-token px-6 py-5 ">
 <h2 class="text-lg font-semibold text-token ">{{ __('Últimas posiciones reportadas') }}</h2>
 </header>
        <div class="overflow-x-auto">
          <table class="table table-md">
            <thead>
              <tr class="table-row">
                <th class="table-header">{{ __('Vehículo') }}</th>
                <th class="table-header">{{ __('Orden') }}</th>
                <th class="table-header">{{ __('Estado') }}</th>
                <th class="table-header">{{ __('Reportado') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($latestTracking as $tracking)
                <tr class="table-row table-row-hover">
                  <td class="table-cell text-sm font-medium text-token ">{{ optional($tracking->truck)->plate_number ?? '—' }}</td>
                  <td class="table-cell text-sm text-token ">{{ optional($tracking->assignment?->order)->reference ?? '—' }}</td>
                  <td class="table-cell text-sm text-token ">{{ __($tracking->status) }}</td>
                  <td class="table-cell text-sm text-token ">{{ optional($tracking->reported_at)?->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>
              @empty
                <tr class="table-row">
                  <td colspan="4" class="table-empty">{{ __('Sin reportes recientes de GPS.') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
 </article>
 </section>
</div>

