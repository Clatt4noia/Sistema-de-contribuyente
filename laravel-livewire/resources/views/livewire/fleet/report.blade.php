<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h1 class="text-2xl font-semibold text-slate-900 ">Reporte de Flota</h1>
 <div class="flex flex-wrap items-center gap-3">
    <button type="button" wire:click="exportPdf" class="btn btn-secondary">
        <i class="fas fa-file-pdf text-danger"></i>
        PDF
    </button>
    <button type="button" wire:click="exportExcel" class="btn btn-secondary">
        <i class="fas fa-file-excel text-success"></i>
        Excel
    </button>
    <a href="{{ route('fleet.assignments.index') }}" class="btn btn-primary">Ver asignaciones</a>
 </div>
 </div>

 <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">Camiones disponibles</p>
 <p class="mt-1 text-2xl font-semibold text-slate-900 ">{{ $truckTotals['available'] ?? 0 }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">Camiones en uso</p>
 <p class="mt-1 text-2xl font-semibold text-slate-900 ">{{ $truckTotals['in_use'] ?? 0 }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">Camiones en mantenimiento</p>
 <p class="mt-1 text-2xl font-semibold text-slate-900 ">{{ $truckTotals['maintenance'] ?? 0 }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-slate-500 ">Pedidos activos</p>
 <p class="mt-1 text-2xl font-semibold text-slate-900 ">{{ ($orderTotals['pending'] ?? 0) + ($orderTotals['en_route'] ?? 0) }}</p>
 </div>
 </div>

 <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
 <div class="surface-card p-4 shadow-sm">
 <h2 class="text-lg font-semibold text-slate-900 ">Conductores</h2>
 <ul class="mt-3 space-y-2 text-sm text-slate-600 ">
 <li>Activos: {{ $driverTotals['active'] ?? 0 }}</li>
 <li>Asignados: {{ $driverTotals['assigned'] ?? 0 }}</li>
 <li>Inactivos: {{ $driverTotals['inactive'] ?? 0 }}</li>
 <li>De permiso: {{ $driverTotals['on_leave'] ?? 0 }}</li>
 </ul>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <h2 class="text-lg font-semibold text-slate-900 ">Asignaciones</h2>
 <ul class="mt-3 space-y-2 text-sm text-slate-600 ">
 <li>Programadas: {{ $assignmentsByStatus['scheduled'] ?? 0 }}</li>
 <li>En ruta: {{ $assignmentsByStatus['in_progress'] ?? 0 }}</li>
 <li>Completadas: {{ $assignmentsByStatus['completed'] ?? 0 }}</li>
 <li>Canceladas: {{ $assignmentsByStatus['cancelled'] ?? 0 }}</li>
 </ul>
 </div>
 </div>

 <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
 <div class="surface-card p-4 shadow-sm">
 <h2 class="text-lg font-semibold text-slate-900 ">Top conductores (mes)</h2>
  <table class="table table-sm mt-3">
    <thead>
      <tr class="table-row">
        <th class="table-header">Conductor</th>
        <th class="table-header">Asignaciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($topDrivers as $driver)
        <tr class="table-row table-row-hover">
          <td class="table-cell text-slate-700 ">{{ $driver->full_name }}</td>
          <td class="table-cell text-slate-700 ">{{ $driver->assignments_count }}</td>
        </tr>
      @empty
        <tr class="table-row">
          <td colspan="2" class="table-empty">Sin asignaciones recientes</td>
        </tr>
      @endforelse
    </tbody>
  </table>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <h2 class="text-lg font-semibold text-slate-900 ">Licencias por vencer (30 dias)</h2>
 <table class="table table-sm mt-3">
 <thead>
 <tr class="table-row">
 <th class="table-header">Conductor</th>
 <th class="table-header">Vence</th>
 </tr>
 </thead>
 <tbody>
 @forelse($licenseAlerts as $driver)
 <tr class="table-row table-row-hover">
 <td class="table-cell text-slate-700 ">{{ $driver->full_name }}</td>
 <td class="table-cell {{ $driver->license_expiration->isPast() ? 'text-danger-strong font-semibold ' : 'text-warning font-semibold' }}">
 {{ $driver->license_expiration->format('d/m/Y') }}
 </td>
 </tr>
 @empty
 <tr class="table-row">
 <td colspan="2" class="table-empty">Sin alertas.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <h2 class="text-lg font-semibold text-slate-900 ">Mantenimientos proximos</h2>
 <table class="table table-sm mt-3">
 <thead>
 <tr class="table-row">
 <th class="table-header">Vehiculo</th>
 <th class="table-header">Fecha</th>
 <th class="table-header">Tipo</th>
 </tr>
 </thead>
 <tbody>
 @forelse($upcomingMaintenance as $item)
 <tr class="table-row table-row-hover">
 <td class="table-cell text-slate-700 ">{{ $item->truck->plate_number }}</td>
 <td class="table-cell text-slate-700 ">{{ $item->maintenance_date->format('d/m/Y') }}</td>
 <td class="table-cell text-slate-700 ">{{ $item->maintenance_type }}</td>
 </tr>
 @empty
 <tr class="table-row">
 <td colspan="3" class="table-empty">No hay mantenimientos programados.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 <div class="surface-card p-4 shadow-sm">
 <h2 class="text-lg font-semibold text-slate-900 ">Documentos críticos</h2>
 <table class="table table-sm mt-3">
 <thead>
 <tr class="table-row">
 <th class="table-header">Recurso</th>
 <th class="table-header">Documento</th>
 <th class="table-header">Vence</th>
 <th class="table-header">Estado</th>
 </tr>
 </thead>
 <tbody>
 @php
 $statusClasses = [
 \App\Models\Document::STATUS_WARNING => 'bg-warning-soft text-warning ',
 \App\Models\Document::STATUS_EXPIRED => 'bg-danger-soft text-danger-strong ',
 \App\Models\Document::STATUS_VALID => 'bg-success-soft text-success-strong ',
 ];
 @endphp
 @forelse($documentAlerts as $document)
 <tr class="table-row table-row-hover">
 <td class="table-cell text-slate-700 ">{{ $document->owner_label }}</td>
 <td class="table-cell text-slate-700 ">{{ $document->title ?: $document->type_label }}</td>
 <td class="table-cell text-slate-700 ">{{ optional($document->expires_at)->format('d/m/Y') ?? '—' }}</td>
 <td class="table-cell">
 <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$document->status] ?? $statusClasses[\App\Models\Document::STATUS_WARNING] }}">{{ $document->status_label }}</span>
 </td>
 </tr>
 @empty
 <tr class="table-row">
 <td colspan="4" class="table-empty">Sin documentos con alertas de vigencia.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
</div>
