<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <div class="space-y-1">
 <h1 class="text-2xl font-semibold text-token ">Gestion de Camiones</h1>
 <p class="text-sm text-token ">Monitorea disponibilidad, mantenimientos y asignaciones de la flota.</p>
 </div>
    <a href="{{ route('fleet.trucks.create') }}" class="btn btn-primary">
        <span class="text-lg leading-none">+</span>
        Agregar Camion
    </a>
 </div>

 @if (session()->has('message'))
 <div class="alert alert-success ">
 {{ session('message') }}
 </div>
 @endif

 <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-token ">Camiones disponibles</p>
 <p class="mt-1 text-2xl font-semibold text-success ">{{ $statusTotals['available'] ?? 0 }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-token ">Camiones en uso</p>
 <p class="mt-1 text-2xl font-semibold text-accent ">{{ $statusTotals['in_use'] ?? 0 }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-token ">En mantenimiento</p>
 <p class="mt-1 text-2xl font-semibold text-warning ">{{ $statusTotals['maintenance'] ?? 0 }}</p>
 </div>
 <div class="surface-card p-4 shadow-sm">
 <p class="text-sm text-token ">Mantenimientos proximos (30 dias)</p>
 <p class="mt-1 text-2xl font-semibold text-danger ">{{ $maintenanceDueSoon }}</p>
 </div>
 </div>

 <div class="surface-card shadow-lg">
 <div class="flex flex-col gap-4 border-b border-token px-4 py-4 md:flex-row md:items-center md:justify-between">
 <div class="w-full md:max-w-md">
 <label class="sr-only" for="truck-search">Buscar</label>
 <input id="truck-search" wire:model.live="search" type="text" placeholder="Buscar por placa, marca o modelo..." class="form-control">
 </div>
 <div class="flex flex-wrap items-center gap-3">
 <select wire:model.live="status" class="form-control md:w-44">
 <option value="">Todos los estados</option>
 <option value="available">Disponible</option>
 <option value="in_use">En uso</option>
 <option value="maintenance">En mantenimiento</option>
 <option value="out_of_service">Fuera de servicio</option>
 </select>
 <button type="button" wire:click="$set('status', '')" class="inline-flex items-center rounded-xl border border-token px-4 py-2 text-sm font-medium text-token transition hover:[background-color:var(--color-surface-muted)] ">
 Limpiar
 </button>
 </div>
 </div>

  <div class="overflow-x-auto">
    <table class="table table-md">
      <thead>
        <tr class="table-row">
          <th class="table-header">Placa</th>
          <th class="table-header">Marca/Modelo</th>
          <th class="table-header">Ano</th>
          <th class="table-header">Tipo</th>
          <th class="table-header">Kilometraje</th>
          <th class="table-header">Estado</th>
          <th class="table-header">Prox. Mant.</th>
          <th class="table-header">Alerta</th>
          <th class="table-header text-center">Pend. Mant.</th>
          <th class="table-header text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($trucks as $truck)
          @php
            $statusStyles = [
              'available' => ['label' => 'Disponible', 'class' => 'bg-success-soft text-success-strong '],
              'in_use' => ['label' => 'En uso', 'class' => 'bg-accent-soft text-accent '],
              'maintenance' => ['label' => 'En mantenimiento', 'class' => 'bg-warning-soft text-warning '],
              'out_of_service' => ['label' => 'Fuera de servicio', 'class' => 'bg-danger-soft text-danger-strong '],
            ];
            $statusConfig = $statusStyles[$truck->status->value] ?? $statusStyles['available'];
            $nextMaintenance = $truck->next_maintenance;
            $isPastDue = $nextMaintenance && $nextMaintenance->isPast();
            $isDueSoon = $nextMaintenance && !$isPastDue && $nextMaintenance->lessThanOrEqualTo(now()->addDays(30));
            $nextClass = $isPastDue
              ? 'text-danger font-semibold '
              : ($isDueSoon
                ? 'text-warning font-semibold '
                : 'text-token ');
            $alertLevel = $truck->maintenanceAlertLevel();
          @endphp
          <tr class="table-row table-row-hover">
            <td class="table-cell font-medium text-token ">{{ $truck->plate_number }}</td>
            <td class="table-cell text-token ">{{ $truck->brand }} {{ $truck->model }}</td>
            <td class="table-cell text-token ">{{ $truck->year }}</td>
            <td class="table-cell text-token ">{{ $truck->type }}</td>
            <td class="table-cell text-token ">{{ number_format($truck->mileage ?? 0) }} km</td>
            <td class="table-cell">
              <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusConfig['class'] }}">
                {{ $statusConfig['label'] }}
              </span>
            </td>
            <td class="table-cell {{ $nextClass }}">
              {{ $nextMaintenance ? $nextMaintenance->format('d/m/Y') : 'No programado' }}
            </td>
            <td class="table-cell">
              <span @class([
                'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold',
                'bg-success-soft text-success-strong ' => $alertLevel === 'ok',
                'bg-warning-soft text-warning ' => $alertLevel === 'warning',
                'bg-danger-soft text-danger-strong ' => $alertLevel === 'danger',
              ])>
                @switch($alertLevel)
                  @case('danger') Requiere mantenimiento @break
                  @case('warning') Revisar pronto @break
                  @default Sin alertas
                @endswitch
              </span>
            </td>
            <td class="table-cell text-center text-token ">{{ $truck->pending_maintenances_count ?? 0 }}</td>
            <td class="table-cell text-right">
              <a href="{{ route('fleet.trucks.edit', $truck) }}" class="font-semibold text-accent transition hover:text-[color:var(--color-primary-emphasis)] ">Editar</a>
              <button wire:click="deleteTruck({{ $truck->id }})" wire:confirm="Esta seguro de eliminar este camion?" class="ml-3 font-semibold text-danger transition hover:text-danger-strong ">Eliminar</button>
            </td>
          </tr>
        @empty
          <tr class="table-row">
            <td colspan="10" class="table-empty">No hay camiones registrados.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="table-footer">
      {{ $trucks->links() }}
    </div>
 </div>
</div>
