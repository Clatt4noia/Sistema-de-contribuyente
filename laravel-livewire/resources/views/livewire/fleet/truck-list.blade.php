<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Gestion de Camiones</h1>
            <p class="text-sm text-slate-500 dark:text-slate-300">Monitorea disponibilidad, mantenimientos y asignaciones de la flota.</p>
        </div>
        <a href="{{ route('fleet.trucks.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:bg-indigo-400 dark:text-slate-900 dark:hover:bg-indigo-300">
            <span class="text-lg leading-none">+</span>
            Agregar Camion
        </a>
    </div>

    @if (session()->has('message'))
        <div class="rounded-2xl border border-emerald-200/70 bg-emerald-50/80 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-slate-500 dark:text-slate-300">Camiones disponibles</p>
            <p class="mt-1 text-2xl font-semibold text-emerald-600 dark:text-emerald-300">{{ $statusTotals['available'] ?? 0 }}</p>
        </div>
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-slate-500 dark:text-slate-300">Camiones en uso</p>
            <p class="mt-1 text-2xl font-semibold text-sky-600 dark:text-sky-300">{{ $statusTotals['in_use'] ?? 0 }}</p>
        </div>
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-slate-500 dark:text-slate-300">En mantenimiento</p>
            <p class="mt-1 text-2xl font-semibold text-amber-600 dark:text-amber-300">{{ $statusTotals['maintenance'] ?? 0 }}</p>
        </div>
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-slate-500 dark:text-slate-300">Mantenimientos proximos (30 dias)</p>
            <p class="mt-1 text-2xl font-semibold text-rose-600 dark:text-rose-300">{{ $maintenanceDueSoon }}</p>
        </div>
    </div>

    <div class="surface-card shadow-lg">
        <div class="flex flex-col gap-4 border-b border-slate-200/70 px-4 py-4 dark:border-slate-800/70 md:flex-row md:items-center md:justify-between">
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
                <button type="button" wire:click="$set('status', '')" class="inline-flex items-center rounded-xl border border-slate-200/70 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700/70 dark:text-slate-200 dark:hover:bg-slate-900/60">
                    Limpiar
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="surface-table">
                <thead>
                    <tr>
                        <th class="px-6 py-3">Placa</th>
                        <th class="px-6 py-3">Marca/Modelo</th>
                        <th class="px-6 py-3">Ano</th>
                        <th class="px-6 py-3">Tipo</th>
                        <th class="px-6 py-3">Kilometraje</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3">Prox. Mant.</th>
                        <th class="px-6 py-3">Alerta</th>
                        <th class="px-6 py-3 text-center">Pend. Mant.</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trucks as $truck)
                        @php
                            $statusStyles = [
                                'available' => ['label' => 'Disponible', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200'],
                                'in_use' => ['label' => 'En uso', 'class' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-200'],
                                'maintenance' => ['label' => 'En mantenimiento', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200'],
                                'out_of_service' => ['label' => 'Fuera de servicio', 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-200'],
                            ];
                            $statusConfig = $statusStyles[$truck->status] ?? $statusStyles['available'];
                            $nextMaintenance = $truck->next_maintenance;
                            $isPastDue = $nextMaintenance && $nextMaintenance->isPast();
                            $isDueSoon = $nextMaintenance && !$isPastDue && $nextMaintenance->lessThanOrEqualTo(now()->addDays(30));
                            $nextClass = $isPastDue
                                ? 'text-rose-600 font-semibold dark:text-rose-300'
                                : ($isDueSoon
                                    ? 'text-amber-600 font-semibold dark:text-amber-300'
                                    : 'text-slate-700 dark:text-slate-300');
                            $alertLevel = $truck->maintenanceAlertLevel();
                        @endphp
                        <tr class="transition hover:bg-slate-900/5 dark:hover:bg-white/10">
                            <td class="px-6 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $truck->plate_number }}</td>
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-300">{{ $truck->brand }} {{ $truck->model }}</td>
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-300">{{ $truck->year }}</td>
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-300">{{ $truck->type }}</td>
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-300">{{ number_format($truck->mileage ?? 0) }} km</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusConfig['class'] }}">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-3 {{ $nextClass }}">
                                {{ $nextMaintenance ? $nextMaintenance->format('d/m/Y') : 'No programado' }}
                            </td>
                            <td class="px-6 py-3">
                                <span @class([
                                    'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200' => $alertLevel === 'ok',
                                    'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200' => $alertLevel === 'warning',
                                    'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-200' => $alertLevel === 'danger',
                                ])>
                                    @switch($alertLevel)
                                        @case('danger') Requiere mantenimiento @break
                                        @case('warning') Revisar pronto @break
                                        @default Sin alertas
                                    @endswitch
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center text-slate-600 dark:text-slate-300">{{ $truck->pending_maintenances_count ?? 0 }}</td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('fleet.trucks.edit', $truck) }}" class="font-semibold text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-300 dark:hover:text-indigo-200">Editar</a>
                                <button wire:click="deleteTruck({{ $truck->id }})" wire:confirm="Esta seguro de eliminar este camion?" class="ml-3 font-semibold text-rose-600 transition hover:text-rose-700 dark:text-rose-300 dark:hover:text-rose-200">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-6 text-center text-slate-500 dark:text-slate-400">No hay camiones registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 px-4 py-3 dark:border-slate-800/60">
            {{ $trucks->links() }}
        </div>
    </div>
</div>
