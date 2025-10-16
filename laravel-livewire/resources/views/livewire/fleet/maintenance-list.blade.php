<div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h1 class="text-2xl font-semibold text-slate-900 ">Mantenimientos de Vehículos</h1>
    <a
        href="{{ route('fleet.maintenance.create') }}"
        class="btn btn-primary"
    >
        Registrar Mantenimiento
    </a>
 </div>

 @if (session()->has('message'))
 <div class="alert alert-success ">
 {{ session('message') }}
 </div>
 @endif

 <div class="surface-card overflow-hidden">
 <div class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50 p-4 md:flex-row md:items-center md:justify-between">
 <div class="flex-1 md:max-w-md">
 <input
 type="text"
 wire:model.live="search"
 placeholder="Buscar por tipo o descripción..."
 class="form-control"
 >
 </div>
 <div class="flex flex-wrap gap-3 text-sm">
 <select wire:model.live="truck_id" class="form-control">
 <option value="">Todos los vehículos</option>
 @foreach($trucks as $truck)
 <option value="{{ $truck->id }}">{{ $truck->plate_number }} - {{ $truck->brand }} {{ $truck->model }}</option>
 @endforeach
 </select>
 <select wire:model.live="status" class="form-control">
 <option value="">Todos los estados</option>
 <option value="scheduled">Programado</option>
 <option value="in_progress">En progreso</option>
 <option value="completed">Completado</option>
 <option value="cancelled">Cancelado</option>
 </select>
 </div>
 </div>

    <div class="overflow-x-auto">
      <table class="table table-md">
        <thead>
          <tr class="table-row">
            <th class="table-header">Vehículo</th>
            <th class="table-header">Fecha</th>
            <th class="table-header">Tipo</th>
            <th class="table-header">Costo</th>
            <th class="table-header">Estado</th>
            <th class="table-header">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($maintenances as $maintenance)
            <tr class="table-row table-row-hover">
              <td class="table-cell">
                <div class="text-sm font-semibold text-slate-800 ">{{ $maintenance->truck->plate_number }}</div>
                <div class="text-xs text-slate-500 ">{{ $maintenance->truck->brand }} {{ $maintenance->truck->model }}</div>
              </td>
              <td class="table-cell text-sm text-slate-600 ">
                {{ $maintenance->maintenance_date->format('d/m/Y') }}
              </td>
              <td class="table-cell text-sm text-slate-600 ">
                {{ $maintenance->maintenance_type }}
              </td>
              <td class="table-cell text-sm text-slate-600 ">
                {{ \App\Support\Formatters\MoneyFormatter::pen($maintenance->cost) }}
              </td>
              <td class="table-cell">
                <span
                  @class([
                    'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold shadow-sm transition',
                    'bg-success-soft text-success-strong ' => $maintenance->status === 'completed',
                    'bg-accent-soft text-accent ' => $maintenance->status === 'in_progress',
                    'bg-warning-soft text-warning ' => $maintenance->status === 'scheduled',
                    'bg-danger-soft text-danger-strong ' => $maintenance->status === 'cancelled',
                  ])
                >
                  {{ $maintenance->status === 'completed' ? 'Completado' : ($maintenance->status === 'in_progress' ? 'En progreso' : ($maintenance->status === 'scheduled' ? 'Programado' : 'Cancelado')) }}
                </span>
              </td>
              <td class="table-cell text-sm font-semibold">
                <a
                  href="{{ route('fleet.maintenance.edit', $maintenance) }}"
                  class="btn btn-ghost btn-sm mr-2"
                >
                  Editar
                </a>
                <button
                  wire:click="deleteMaintenance({{ $maintenance->id }})"
                  wire:confirm="¿Está seguro de eliminar este registro?"
                  class="btn btn-danger btn-sm"
                >
                  Eliminar
                </button>
              </td>
            </tr>
          @empty
            <tr class="table-row">
              <td colspan="6" class="table-empty">
                No se encontraron registros de mantenimiento.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>


                  ])
                >
                  {{ $maintenance->status === 'completed' ? 'Completado' : ($maintenance->status === 'in_progress' ? 'En progreso' : ($maintenance->status === 'scheduled' ? 'Programado' : 'Cancelado')) }}
                </span>
              </td>
              <td class="table-cell text-sm font-semibold">
                <a
                  href="{{ route('fleet.maintenance.edit', $maintenance) }}"
                  class="btn btn-ghost btn-sm mr-2"
                >
                  Editar
                </a>
                <button
                  wire:click="deleteMaintenance({{ $maintenance->id }})"
                  wire:confirm="¿Está seguro de eliminar este registro?"
                  class="btn btn-danger btn-sm"
                >
                  Eliminar
                </button>
              </td>
            </tr>
          @empty
            <tr class="table-row">
              <td colspan="6" class="table-empty">
                No se encontraron registros de mantenimiento.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>


    <div class="table-footer text-sm text-slate-600 ">
      {{ $maintenances->links() }}
    </div>
 </div>
</div>
