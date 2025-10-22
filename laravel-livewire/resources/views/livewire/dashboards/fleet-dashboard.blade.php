<div class="space-y-6">
<section class="grid w-full gap-6 px-6 mb-6">
  <article class="surface-card w-full">


 <header class="flex items-center justify-between border-b border-token px-6 py-5 ">
 <div>
 <h1 class="text-2xl font-semibold text-token ">{{ __('Salud de la flota') }}</h1>
 <p class="mt-1 text-sm text-token ">{{ __('Disponibilidad, mantenimientos y documentos clave.') }}</p>
 </div>
 </header>
 <div class="grid gap-4 p-6 sm:grid-cols-3">
 <x-dashboard.stat :label="__('Disponibles')" :value="$fleetStats['available']" icon="truck" />
 <x-dashboard.stat :label="__('En mantenimiento')" :value="$fleetStats['inMaintenance']" icon="timer" />
 <x-dashboard.stat :label="__('Documentos por vencer')" :value="$fleetStats['expiringDocuments']" icon="alert-circle" />
 </div>
 <div class="border-t border-token px-6 py-6 ">
 <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
 <div class="xl:col-span-1">
 <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-token0 ">{{ __('Distribución de la flota') }}</h3>
 <div class="relative h-60">
 <canvas id="fleet-status-chart" aria-label="{{ __('Distribución de estados de la flota') }}" role="img"></canvas>
 </div>
 </div>

 <div class="md:col-span-2 xl:col-span-1">
 <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-token0 ">{{ __('Mantenimientos por mes') }}</h3>
 <div class="relative h-60">
 <canvas id="maintenance-trend-chart" aria-label="{{ __('Tendencia de mantenimientos programados') }}" role="img"></canvas>
 </div>
 </div>

 <div class="md:col-span-2 xl:col-span-1">
 <div class="flex items-center justify-between">
 <h3 class="text-sm font-semibold uppercase tracking-wide text-token0 ">{{ __('Asignaciones por chofer') }}</h3>
<span class="rounded-full bg-[color:var(--color-primary-100)] bg-opacity-10 px-3 py-1 text-xs font-semibold text-token">
    {{ __('Promedio: :value', ['value' => $assignmentsAverage]) }}
</span>

 </div>
 <div class="relative mt-4 h-60">
 <canvas id="driver-assignments-chart" aria-label="{{ __('Top de asignaciones por chofer') }}" role="img"></canvas>
 </div>
 <p class="mt-3 text-xs text-token0 ">{{ __('Basado en los últimos 90 días de operaciones.') }}</p>
 </div>
 </div>
 </div>
 </article>
 </section>

 <section class="grid gap-6 lg:grid-cols-2">
 <article class="surface-card">
 <header class="border-b border-token px-6 py-5 ">
 <h2 class="text-lg font-semibold text-token ">{{ __('Mantenimientos próximos') }}</h2>
 </header>
    <div class="overflow-x-auto">
      <table class="table table-md">
        <thead>
          <tr class="table-row">
            <th class="table-header">{{ __('Camión') }}</th>
            <th class="table-header">{{ __('Tipo') }}</th>
            <th class="table-header">{{ __('Fecha') }}</th>
            <th class="table-header">{{ __('Responsable') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($upcomingMaintenances as $maintenance)
            <tr class="table-row table-row-hover">
              <td class="table-cell text-sm font-medium text-token ">{{ optional($maintenance->truck)->plate_number ?? '—' }}</td>
              <td class="table-cell text-sm text-token ">{{ $maintenance->type ?? '—' }}</td>
              <td class="table-cell text-sm text-token ">{{ optional($maintenance->scheduled_at)?->format('d/m/Y') ?? '—' }}</td>
              <td class="table-cell text-sm text-token ">{{ optional($maintenance->responsible)->name ?? '—' }}</td>
            </tr>
          @empty
            <tr class="table-row">
              <td colspan="4" class="table-empty">{{ __('No hay mantenimientos programados.') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
 </article>

 <article class="surface-card">
 <header class="border-b border-token px-6 py-5 ">
 <h2 class="text-lg font-semibold text-token ">{{ __('Documentos próximos a vencer') }}</h2>
 </header>
    <div class="overflow-x-auto">
      <table class="table table-md">
        <thead>
          <tr class="table-row">
            <th class="table-header">{{ __('Recurso') }}</th>
            <th class="table-header">{{ __('Documento') }}</th>
            <th class="table-header">{{ __('Vencimiento') }}</th>
            <th class="table-header">{{ __('Estado') }}</th>
          </tr>
        </thead>
        <tbody>
          @php
            $badgeStyles = [
              \App\Models\Document::STATUS_VALID => 'bg-success-soft text-success-strong ',
              \App\Models\Document::STATUS_WARNING => 'bg-warning-soft text-warning ',
              \App\Models\Document::STATUS_EXPIRED => 'bg-danger-soft text-danger-strong ',
            ];
          @endphp
          @forelse ($expiringDocuments as $document)
            <tr class="table-row table-row-hover">
              <td class="table-cell text-sm font-medium text-token ">{{ $document->resource_label }}</td>
              <td class="table-cell text-sm text-token ">{{ $document->name ?? '—' }}</td>
              <td class="table-cell text-sm text-token ">{{ optional($document->expires_at)?->format('d/m/Y') ?? '—' }}</td>
              <td class="table-cell">
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badgeStyles[$document->status] ?? $badgeStyles[\App\Models\Document::STATUS_WARNING] }}">{{ $document->status_label ?? __('Pendiente') }}</span>
              </td>
            </tr>
          @empty
            <tr class="table-row">
              <td colspan="4" class="table-empty">{{ __('Registra documentos para mantener trazabilidad.') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
 </article>
 </section>

</div>

@php
 $chartPayloads = [
 'status' => $statusChart,
 'maintenance' => $maintenanceTrend,
 'assignments' => $assignmentsChart,
 ];
@endphp

<script>
 document.addEventListener('livewire:init', () => {
 const chartPayloads = @json($chartPayloads);

 const chartFactory = () => {
 if (typeof window === 'undefined' || typeof window.Chart === 'undefined') {
 return;
 }

 const registryKey = '__fleetDashboardCharts';
 window[registryKey] = window[registryKey] || {};
 const registry = window[registryKey];

 const createOrUpdate = (id, config) => {
 const canvas = document.getElementById(id);
 if (!canvas) {
 return;
 }

 const context = canvas.getContext('2d');

 if (registry[id]) {
 registry[id].data = config.data;
 registry[id].options = config.options || {};
 registry[id].update();
 return;
 }

 registry[id] = new window.Chart(context, config);
 };

 createOrUpdate('fleet-status-chart', {
 type: 'doughnut',
 data: chartPayloads.status,
 options: {
 responsive: true,
 maintainAspectRatio: false,
 plugins: {
 legend: {
 position: 'bottom',
 labels: {
 usePointStyle: true,
 },
 },
 },
 cutout: '60%',
 },
 });

 createOrUpdate('maintenance-trend-chart', {
 type: 'line',
 data: chartPayloads.maintenance,
 options: {
 responsive: true,
 maintainAspectRatio: false,
 scales: {
 y: {
 beginAtZero: true,
 ticks: {
 precision: 0,
 },
 grid: {
 color: 'rgba(148, 163, 184, 0.2)',
 },
 },
 x: {
 grid: {
 display: false,
 },
 },
 },
 plugins: {
 legend: {
 display: false,
 },
 },
 },
 });

 createOrUpdate('driver-assignments-chart', {
 type: 'bar',
 data: chartPayloads.assignments,
 options: {
 responsive: true,
 maintainAspectRatio: false,
 scales: {
 y: {
 beginAtZero: true,
 ticks: {
 precision: 0,
 },
 grid: {
 color: 'rgba(148, 163, 184, 0.2)',
 },
 },
 x: {
 grid: {
 display: false,
 },
 },
 },
 plugins: {
 legend: {
 display: false,
 },
 },
 },
 });
 };

 chartFactory();

 Livewire.hook('message.processed', (message, component) => {
 if (component.fingerprint.name === 'dashboards.fleet-dashboard') {
 chartFactory();
 }
 });
 });
</script>

