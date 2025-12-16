<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

  <!-- Header -->
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div class="space-y-1">
      <h1 class="text-2xl font-semibold text-token">Gestión de Choferes</h1>
      <p class="text-sm text-token/70">
        Controla licencias, horarios, evaluaciones y capacitaciones del personal.
      </p>
    </div>

    <a href="{{ route('fleet.drivers.create') }}" class="btn btn-primary">
      Agregar Chofer
    </a>
  </div>

  <!-- Flash -->
  @if (session()->has('message'))
    <div class="alert alert-success" role="alert">
      <p>{{ session('message') }}</p>
    </div>
  @endif

  <!-- Card -->
  <div class="surface-card overflow-hidden rounded-xl border border-black/5 shadow-lg">

    <!-- Toolbar -->
    <div class="flex flex-col gap-4 border-b border-token px-4 py-4 md:flex-row md:items-center md:justify-between">
      <div class="w-full md:max-w-md">
        <label class="sr-only" for="driver-search">Buscar</label>
        <div class="relative">
          <input
            id="driver-search"
            type="text"
            wire:model.live="search"
            placeholder="Buscar por nombre, documento o licencia..."
            class="form-control pl-10"
          >
          <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-token/50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M8.5 3.5a5 5 0 103.79 8.27l3.22 3.22a1 1 0 001.42-1.42l-3.22-3.22A5 5 0 008.5 3.5zM5.5 8.5a3 3 0 116 0 3 3 0 01-6 0z" clip-rule="evenodd"/>
            </svg>
          </span>
        </div>
      </div>

      <div class="flex-none md:w-56">
        <select wire:model.live="status" class="form-control">
          <option value="">Todos los estados</option>
          <option value="active">Activo</option>
          <option value="inactive">Inactivo</option>
          <option value="on_leave">De permiso</option>
          <option value="assigned">Asignado</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="relative overflow-x-auto">
      <table class="table table-md w-full min-w-[1100px]">
        <thead class="bg-[color:var(--color-surface-muted)]">
          <tr class="table-row">
            <!-- Sticky izquierda: mejora lectura (opcional, pero recomendado) -->
            <th class="table-header sticky left-0 z-20 bg-[color:var(--color-surface-muted)]">Chofer</th>

            <th class="table-header whitespace-nowrap">Documento</th>
            <th class="table-header whitespace-nowrap">Licencia</th>
            <th class="table-header whitespace-nowrap">Vencimiento</th>
            <th class="table-header">Horarios</th>
            <th class="table-header whitespace-nowrap">Estado</th>

            <!-- Columnas opcionales (visibles desde XL) -->
            <th class="table-header hidden xl:table-cell whitespace-nowrap">Evaluación</th>
            <th class="table-header hidden xl:table-cell">Capacitaciones</th>

            <!-- Sticky derecha: Acciones siempre visibles -->
            <th class="table-header sticky right-0 z-20 bg-[color:var(--color-surface-muted)] text-right">
              Acciones
            </th>
          </tr>
        </thead>

        <tbody>
          @forelse ($drivers as $driver)
            @php
              $statusStyles = [
                'active' => ['label' => 'Activo', 'class' => 'bg-success-soft text-success-strong'],
                'inactive' => ['label' => 'Inactivo', 'class' => 'bg-danger-soft text-danger-strong'],
                'on_leave' => ['label' => 'De permiso', 'class' => 'bg-warning-soft text-warning'],
                'assigned' => ['label' => 'Asignado', 'class' => 'bg-accent-soft text-accent'],
              ];
              $statusConfig = $statusStyles[$driver->status] ?? $statusStyles['active'];

              $scheduleSummary = $driver->schedules
                ->map(fn ($s) => substr($s->day_of_week, 0, 3) . ' ' . ($s->start_time?->format('H:i') ?? '') . '-' . ($s->end_time?->format('H:i') ?? ''))
                ->filter()
                ->implode(', ');

              $averageScore = $driver->evaluations->isNotEmpty()
                ? round($driver->evaluations->avg('score'), 2)
                : null;

              $validTrainings = $driver->trainings->filter(fn($t) => ! $t->expires_at || $t->expires_at->isFuture());
              $expiringTraining = $driver->trainings->first(fn($t) => $t->expires_at && $t->expires_at->diffInDays(now(), false) >= -30 && $t->expires_at->isFuture());

              $licenseIsPast = $driver->license_expiration->isPast();
              $licenseDueSoon = ! $licenseIsPast && $driver->license_expiration->diffInDays(now()) < 30;
            @endphp

            <tr class="table-row table-row-hover">
              <!-- Sticky izquierda -->
              <td class="table-cell sticky left-0 z-10 bg-[color:var(--color-surface)]">
                <div class="whitespace-nowrap text-sm font-medium text-token">{{ $driver->full_name }}</div>
                <div class="mt-0.5 text-xs text-token/60">Lic. {{ $driver->license_number }}</div>
              </td>

              <td class="table-cell whitespace-nowrap text-sm text-token">{{ $driver->document_number }}</td>
              <td class="table-cell whitespace-nowrap text-sm text-token">{{ $driver->license_number }}</td>

              <td class="table-cell whitespace-nowrap text-sm text-token">
                {{ $driver->license_expiration->format('d/m/Y') }}

                @if($licenseIsPast)
                  <span class="ml-2 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold bg-danger-soft text-danger-strong">
                    Vencida
                  </span>
                @elseif($licenseDueSoon)
                  <span class="ml-2 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold bg-warning-soft text-warning">
                    Próxima
                  </span>
                @endif
              </td>

              <td class="table-cell text-sm text-token">
                <div class="flex items-center justify-between gap-3">
               

                  <button
                    type="button"
                    wire:click="openDriverModal({{ $driver->id }})"
                    class="btn btn-ghost btn-sm"
                  >
                    Ver
                  </button>
                </div>
              </td>

              <td class="table-cell whitespace-nowrap">
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusConfig['class'] }}">
                  {{ $statusConfig['label'] }}
                </span>
              </td>

              <!-- Opcionales desde XL -->
              <td class="table-cell hidden xl:table-cell whitespace-nowrap text-sm text-token">
                {{ $averageScore ? $averageScore . ' / 5' : 'Sin evaluaciones' }}
              </td>

              <td class="table-cell hidden xl:table-cell text-sm text-token">
                <div class="flex flex-col gap-1">
                  <span class="inline-flex items-center gap-2 text-xs font-semibold">
                    <span class="inline-flex h-2 w-2 rounded-full bg-[color:var(--color-primary)]"></span>
                    {{ $validTrainings->count() }} vigentes
                  </span>
                  @if ($expiringTraining)
                    <span class="inline-flex items-center gap-2 text-xs font-medium text-warning">
                      <span class="inline-flex h-2 w-2 rounded-full bg-warning-soft0"></span>
                      <span class="block max-w-[260px] truncate" title="{{ $expiringTraining->name }}">
                        {{ $expiringTraining->name }}
                      </span>
                      vence {{ $expiringTraining->expires_at?->format('d/m/Y') }}
                    </span>
                  @endif
                </div>
              </td>

              <!-- Sticky derecha -->
              <td class="table-cell sticky right-0 z-10 bg-[color:var(--color-surface)] whitespace-nowrap text-right">
                <div class="inline-flex items-center gap-2">
                  <a href="{{ route('fleet.drivers.edit', $driver) }}" class="btn btn-ghost btn-sm">
                    Editar
                  </a>

                  <button
                    type="button"
                    wire:click="deleteDriver({{ $driver->id }})"
                    wire:confirm="Esta seguro de eliminar este chofer?"
                    class="btn btn-danger btn-sm"
                  >
                    Eliminar
                  </button>
                </div>
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

    <div class="table-footer px-4 py-4">
      {{ $drivers->links() }}
    </div>
  </div>

  <!-- Modal -->
  @if($driverModalOpen)
    <div class="fixed inset-0 z-50">
      <!-- Overlay -->
      <div class="absolute inset-0 bg-black/40" wire:click="closeDriverModal"></div>

      <div class="relative mx-auto mt-16 w-[calc(100%-2rem)] max-w-3xl">
        <div class="surface-card rounded-2xl border border-black/10 shadow-xl">
          <div class="flex items-start justify-between gap-4 border-b border-token px-5 py-4">
            <div>
              <h3 class="text-lg font-semibold text-token">{{ $selectedDriver?->full_name }}</h3>
              <p class="mt-1 text-sm text-token/70">
                Doc: {{ $selectedDriver?->document_number }} · Lic: {{ $selectedDriver?->license_number }}
              </p>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" wire:click="closeDriverModal">Cerrar</button>
          </div>

          <div class="grid gap-5 px-5 py-5 md:grid-cols-2">
            <div>
              <p class="text-sm text-token/70">Vencimiento de licencia</p>
              <p class="mt-1 text-sm text-token">{{ $selectedDriver?->license_expiration?->format('d/m/Y') ?? '-' }}</p>
            </div>

            <div>
              <p class="text-sm text-token/70">Estado</p>
              <p class="mt-1 text-sm text-token">{{ $selectedDriver?->status ?? '-' }}</p>
            </div>

            <div class="md:col-span-2">
              <p class="text-sm font-semibold text-token">Horarios</p>
              <div class="mt-2 rounded-lg border border-black/5 p-3">
                @if($selectedDriver?->schedules?->isNotEmpty())
                  <ul class="space-y-2 text-sm text-token">
                    @foreach($selectedDriver->schedules as $s)
                      <li class="flex items-center justify-between gap-3">
                        <span class="font-medium">{{ $s->day_of_week }}</span>
                        <span class="text-token/70">
                          {{ $s->start_time?->format('H:i') ?? '' }} - {{ $s->end_time?->format('H:i') ?? '' }}
                        </span>
                      </li>
                    @endforeach
                  </ul>
                @else
                  <p class="text-sm text-token/70">Sin horarios registrados.</p>
                @endif
              </div>
            </div>

            <div>
              <p class="text-sm font-semibold text-token">Evaluaciones</p>
              <p class="mt-2 text-sm text-token">
                @if($selectedDriver?->evaluations?->isNotEmpty())
                  Promedio: {{ round($selectedDriver->evaluations->avg('score'), 2) }} / 5
                @else
                  Sin evaluaciones
                @endif
              </p>
            </div>

            <div>
              <p class="text-sm font-semibold text-token">Capacitaciones</p>
              <div class="mt-2 space-y-2 text-sm text-token">
                @if($selectedDriver?->trainings?->isNotEmpty())
                  <ul class="space-y-1">
                    @foreach($selectedDriver->trainings as $t)
                      <li class="flex items-center justify-between gap-3">
                        <span class="truncate">{{ $t->name }}</span>
                        <span class="text-token/70 whitespace-nowrap">
                          {{ $t->expires_at ? $t->expires_at->format('d/m/Y') : 'Sin venc.' }}
                        </span>
                      </li>
                    @endforeach
                  </ul>
                @else
                  <p class="text-token/70">Sin capacitaciones.</p>
                @endif
              </div>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 border-t border-token px-5 py-4">
            <a href="{{ $selectedDriver ? route('fleet.drivers.edit', $selectedDriver) : '#' }}" class="btn btn-primary">
              Editar chofer
            </a>
          </div>
        </div>
      </div>
    </div>
  @endif

</div>
