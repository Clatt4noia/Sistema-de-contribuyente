<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Gestion de Choferes</h1>
        <a href="{{ route('fleet.drivers.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:bg-indigo-400 dark:text-slate-900 dark:hover:bg-indigo-300">
            Agregar Chofer
        </a>
    </div>

    @if (session()->has('message'))
        <div class="rounded-2xl border border-emerald-200/70 bg-emerald-50/80 p-4 text-sm font-medium text-emerald-700 shadow-sm dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <div class="surface-card overflow-hidden shadow-lg">
        <div class="flex flex-col gap-4 border-b border-slate-200/70 px-4 py-4 dark:border-slate-800/70 md:flex-row md:items-center md:justify-between">
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
            <table class="surface-table">
                <thead>
                    <tr>
                        <th class="px-6 py-3">Nombre</th>
                        <th class="px-6 py-3">Documento</th>
                        <th class="px-6 py-3">Licencia</th>
                        <th class="px-6 py-3">Vencimiento</th>
                        <th class="px-6 py-3">Horarios</th>
                        <th class="px-6 py-3">Evaluacion</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($drivers as $driver)
                        @php
                            $statusStyles = [
                                'active' => ['label' => 'Activo', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200'],
                                'inactive' => ['label' => 'Inactivo', 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-200'],
                                'on_leave' => ['label' => 'De permiso', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200'],
                                'assigned' => ['label' => 'Asignado', 'class' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-200'],
                            ];
                            $statusConfig = $statusStyles[$driver->status] ?? $statusStyles['active'];
                            $scheduleSummary = $driver->schedules->map(fn ($schedule) => substr($schedule->day_of_week, 0, 3) . ' ' . ($schedule->start_time?->format('H:i') ?? '') . '-' . ($schedule->end_time?->format('H:i') ?? ''))->filter()->implode(', ');
                            $averageScore = $driver->evaluations->isNotEmpty() ? round($driver->evaluations->avg('score'), 2) : null;
                        @endphp
                        <tr class="transition hover:bg-slate-900/5 dark:hover:bg-white/10">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-slate-100">{{ $driver->full_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">{{ $driver->document_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">{{ $driver->license_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                {{ $driver->license_expiration->format('d/m/Y') }}
                                @if($driver->license_expiration->isPast())
                                    <span class="ml-2 font-semibold text-rose-500 dark:text-rose-300">VENCIDA</span>
                                @elseif($driver->license_expiration->diffInDays(now()) < 30)
                                    <span class="ml-2 font-semibold text-amber-500 dark:text-amber-300">PROXIMA A VENCER</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                {{ $scheduleSummary ?: 'Sin horarios' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                {{ $averageScore ? $averageScore . ' / 5' : 'Sin evaluaciones' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusConfig['class'] }}">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('fleet.drivers.edit', $driver) }}" class="mr-3 font-semibold text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-300 dark:hover:text-indigo-200">Editar</a>
                                <button wire:click="deleteDriver({{ $driver->id }})" wire:confirm="Esta seguro de eliminar este chofer?" class="font-semibold text-rose-600 transition hover:text-rose-700 dark:text-rose-300 dark:hover:text-rose-200">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-slate-500 dark:text-slate-400">No se encontraron choferes</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200/70 px-4 py-3 dark:border-slate-800/70">
            {{ $drivers->links() }}
        </div>
    </div>
</div>
