<div class="container mx-auto py-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Gestion de Camiones</h1>
            <p class="text-sm text-slate-500">Monitorea disponibilidad, mantenimientos y asignaciones de la flota.</p>
        </div>
        <a href="{{ route('fleet.trucks.create') }}" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
            <span class="text-lg leading-none">+</span>
            Agregar Camion
        </a>
    </div>

    @if (session()->has('message'))
        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Camiones disponibles</p>
            <p class="mt-1 text-2xl font-semibold text-emerald-600">{{ $statusTotals['available'] ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Camiones en uso</p>
            <p class="mt-1 text-2xl font-semibold text-sky-600">{{ $statusTotals['in_use'] ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">En mantenimiento</p>
            <p class="mt-1 text-2xl font-semibold text-amber-600">{{ $statusTotals['maintenance'] ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Mantenimientos proximos (30 dias)</p>
            <p class="mt-1 text-2xl font-semibold text-rose-600">{{ $maintenanceDueSoon }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-4 py-4 md:flex-row md:items-center md:justify-between">
            <div class="w-full md:max-w-md">
                <label class="sr-only" for="truck-search">Buscar</label>
                <input id="truck-search" wire:model.live="search" type="text" placeholder="Buscar por placa, marca o modelo..." class="w-full rounded-md border border-slate-200 px-4 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring focus:ring-blue-100">
            </div>
            <div class="flex items-center gap-3">
                <select wire:model.live="status" class="rounded-md border border-slate-200 px-4 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring focus:ring-blue-100">
                    <option value="">Todos los estados</option>
                    <option value="available">Disponible</option>
                    <option value="in_use">En uso</option>
                    <option value="maintenance">En mantenimiento</option>
                    <option value="out_of_service">Fuera de servicio</option>
                </select>
                <button type="button" wire:click="$set('status', '')" class="rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">
                    Limpiar
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-6 py-3">Placa</th>
                        <th class="px-6 py-3">Marca/Modelo</th>
                        <th class="px-6 py-3">Ano</th>
                        <th class="px-6 py-3">Tipo</th>
                        <th class="px-6 py-3">Kilometraje</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3">Prox. Mant.</th>
                        <th class="px-6 py-3 text-center">Pend. Mant.</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($trucks as $truck)
                        @php
                            $statusStyles = [
                                'available' => ['label' => 'Disponible', 'class' => 'bg-emerald-100 text-emerald-700'],
                                'in_use' => ['label' => 'En uso', 'class' => 'bg-sky-100 text-sky-700'],
                                'maintenance' => ['label' => 'En mantenimiento', 'class' => 'bg-amber-100 text-amber-700'],
                                'out_of_service' => ['label' => 'Fuera de servicio', 'class' => 'bg-rose-100 text-rose-700'],
                            ];
                            $statusConfig = $statusStyles[$truck->status] ?? $statusStyles['available'];
                            $nextMaintenance = $truck->next_maintenance;
                            $isPastDue = $nextMaintenance && $nextMaintenance->isPast();
                            $isDueSoon = $nextMaintenance && !$isPastDue && $nextMaintenance->lessThanOrEqualTo(now()->addDays(30));
                            $nextClass = $isPastDue ? 'text-rose-600 font-semibold' : ($isDueSoon ? 'text-amber-600 font-semibold' : 'text-slate-700');
                        @endphp
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-6 py-3 font-medium text-slate-900">{{ $truck->plate_number }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $truck->brand }} {{ $truck->model }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $truck->year }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $truck->type }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ number_format($truck->mileage ?? 0) }} km</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusConfig['class'] }}">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-3 {{ $nextClass }}">
                                {{ $nextMaintenance ? $nextMaintenance->format('d/m/Y') : 'No programado' }}
                            </td>
                            <td class="px-6 py-3 text-center text-slate-600">{{ $truck->pending_maintenances_count ?? 0 }}</td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('fleet.trucks.edit', $truck) }}" class="text-indigo-600 hover:text-indigo-800">Editar</a>
                                <button wire:click="deleteTruck({{ $truck->id }})" wire:confirm="Esta seguro de eliminar este camion?" class="ml-3 text-rose-600 hover:text-rose-700">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-6 text-center text-slate-500">No hay camiones registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 px-4 py-3">
            {{ $trucks->links() }}
        </div>
    </div>
</div>
