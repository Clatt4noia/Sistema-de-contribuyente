<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h1 class="text-2xl font-semibold text-slate-900 ">Gestion de Choferes</h1>
    <a href="{{ route('fleet.drivers.create') }}" class="btn btn-primary">
        Agregar Chofer
    </a>
 </div>

 @if (session()->has('message'))
 <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700 shadow-sm " role="alert">
 <p>{{ session('message') }}</p>
 </div>
 @endif

 <div class="surface-card overflow-hidden shadow-lg">
 <div class="flex flex-col gap-4 border-b border-slate-200 px-4 py-4 md:flex-row md:items-center md:justify-between">
 <div class="w-full md:max-w-md">
 <input type="text" wire:model.live="search" placeholder="Buscar por nombre, documento o licencia..." class="form-control">
 </div>
 <div class="flex-none md:w-48">
 <select wire:model.live="status" class="form-control">
 <option value="">Todos los estados</option>
 <option value="active">Activo</option>
 <option value="inactive">Inactivo</option>
 <option value="on_leave">De permiso</option>
 <option value="assigned">Asignado</option>
 </select>
 </div>
 </div>

    <div class="overflow-x-auto">
      <table class="table table-md">
        <thead>
          <tr class="table-row">
            <th class="table-header">Nombre</th>
            <th class="table-header">Documento</th>
            <th class="table-header">Licencia</th>
            <th class="table-header">Vencimiento</th>
            <th class="table-header">Horarios</th>
            <th class="table-header">Evaluacion</th>
            <th class="table-header">Capacitaciones</th>
            <th class="table-header">Estado</th>
            <th class="table-header">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($drivers as $driver)
            @php
              $statusStyles = [
                'active' => ['label' => 'Activo', 'class' => 'bg-emerald-100 text-emerald-700 '],
                'inactive' => ['label' => 'Inactivo', 'class' => 'bg-rose-100 text-rose-700 '],
                'on_leave' => ['label' => 'De permiso', 'class' => 'bg-amber-100 text-amber-700 '],
                'assigned' => ['label' => 'Asignado', 'class' => 'bg-sky-100 text-sky-700 '],
              ];
              $statusConfig = $statusStyles[$driver->status] ?? $statusStyles['active'];
              $scheduleSummary = $driver->schedules->map(fn ($schedule) => substr($schedule->day_of_week, 0, 3) . ' ' . ($schedule->start_time?->format('H:i') ?? '') . '-' . ($schedule->end_time?->format('H:i') ?? ''))->filter()->implode(', ');
              $averageScore = $driver->evaluations->isNotEmpty() ? round($driver->evaluations->avg('score'), 2) : null;
              $validTrainings = $driver->trainings->filter(fn($training) => ! $training->expires_at || $training->expires_at->isFuture());
              $expiringTraining = $driver->trainings->first(fn($training) => $training->expires_at && $training->expires_at->diffInDays(now(), false) >= -30 && $training->expires_at->isFuture());
            @endphp
            <tr class="table-row table-row-hover">
              <td class="table-cell whitespace-nowrap text-sm font-medium text-slate-900 ">{{ $driver->full_name }}</td>
              <td class="table-cell whitespace-nowrap text-sm text-slate-600 ">{{ $driver->document_number }}</td>
              <td class="table-cell whitespace-nowrap text-sm text-slate-600 ">{{ $driver->license_number }}</td>
              <td class="table-cell whitespace-nowrap text-sm text-slate-600 ">
                {{ $driver->license_expiration->format('d/m/Y') }}
                @if($driver->license_expiration->isPast())
                  <span class="ml-2 font-semibold text-rose-500 ">VENCIDA</span>
                @elseif($driver->license_expiration->diffInDays(now()) < 30)
                  <span class="ml-2 font-semibold text-amber-500 ">PROXIMA A VENCER</span>
                @endif
              </td>
              <td class="table-cell text-sm text-slate-600 ">
                {{ $scheduleSummary ?: 'Sin horarios' }}
              </td>
              <td class="table-cell whitespace-nowrap text-sm text-slate-600 ">
                {{ $averageScore ? $averageScore . ' / 5' : 'Sin evaluaciones' }}
              </td>
              <td class="table-cell text-sm text-slate-600 ">
                <div class="flex flex-col gap-1">
                  <span class="inline-flex items-center gap-2 text-xs font-semibold">
                    <span class="inline-flex h-2 w-2 rounded-full bg-indigo-500"></span>
                    {{ $validTrainings->count() }} vigentes
                  </span>
                  @if ($expiringTraining)
                    <span class="inline-flex items-center gap-2 text-xs font-medium text-amber-600 ">
                      <span class="inline-flex h-2 w-2 rounded-full bg-amber-500"></span>
                      {{ $expiringTraining->name }} vence {{ $expiringTraining->expires_at?->format('d/m/Y') }}
                    </span>
                  @endif
                </div>
              </td>
              <td class="table-cell whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusConfig['class'] }}">
                  {{ $statusConfig['label'] }}
                </span>
              </td>
              <td class="table-cell whitespace-nowrap text-sm font-medium">
                <a href="{{ route('fleet.drivers.edit', $driver) }}" class="btn btn-ghost btn-sm mr-2">Editar</a>
                <button wire:click="deleteDriver({{ $driver->id }})" wire:confirm="Esta seguro de eliminar este chofer?" class="btn btn-danger btn-sm">Eliminar</button>
              </td>
            </tr>
          @empty
            <tr class="table-row">
              <td colspan="9" class="table-empty">No se encontraron choferes</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="table-footer">
      {{ $drivers->links() }}
    </div>
  </div>
</div>
