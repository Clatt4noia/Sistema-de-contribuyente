<div class="mx-auto max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-8" wire:poll.20s>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Disponibilidad de recursos</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Actualiza automáticamente cada 20 segundos.</p>
        </div>
        <a href="{{ route('fleet.assignments.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-600 dark:bg-indigo-400 dark:text-slate-900 dark:hover:bg-indigo-300">
            <i class="fas fa-plus"></i>
            Crear asignación
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $truckStates = [
                'available' => ['label' => 'Disponibles', 'icon' => 'fa-truck', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200'],
                'in_use' => ['label' => 'En ruta', 'icon' => 'fa-road', 'class' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-200'],
                'maintenance' => ['label' => 'En mantenimiento', 'icon' => 'fa-screwdriver-wrench', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200'],
                'out_of_service' => ['label' => 'Fuera de servicio', 'icon' => 'fa-ban', 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-200'],
            ];

            $driverStates = [
                'active' => ['label' => 'Disponibles', 'icon' => 'fa-id-badge', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200'],
                'assigned' => ['label' => 'Asignados', 'icon' => 'fa-route', 'class' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-200'],
                'on_leave' => ['label' => 'En permiso', 'icon' => 'fa-plane-departure', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200'],
                'inactive' => ['label' => 'Inactivos', 'icon' => 'fa-user-slash', 'class' => 'bg-slate-200 text-slate-700 dark:bg-slate-700/40 dark:text-slate-200'],
            ];
        @endphp

        @foreach ($truckStates as $key => $meta)
            <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm transition hover:shadow-md dark:border-slate-800/70 dark:bg-slate-900/70">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900/5 text-slate-600 dark:bg-white/10 dark:text-slate-200">
                        <i class="fas {{ $meta['icon'] }}"></i>
                    </span>
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Camiones {{ $meta['label'] }}</p>
                        <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $truckStats[$key] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        @endforeach

        @foreach ($driverStates as $key => $meta)
            <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm transition hover:shadow-md dark:border-slate-800/70 dark:bg-slate-900/70">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900/5 text-slate-600 dark:bg-white/10 dark:text-slate-200">
                        <i class="fas {{ $meta['icon'] }}"></i>
                    </span>
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Choferes {{ $meta['label'] }}</p>
                        <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $driverStats[$key] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        <section class="space-y-4">
            <header class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Camiones</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Filtra por placa, marca o estado operativo.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <select wire:model.live="vehicleStatus" class="form-control min-w-[160px]">
                        <option value="">Todos</option>
                        @foreach ($truckStates as $key => $meta)
                            <option value="{{ $key }}">{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                    <input type="search" wire:model.live="vehicleSearch" placeholder="Buscar placa o modelo" class="form-control min-w-[200px]">
                </div>
            </header>

            <div class="space-y-3">
                @forelse ($trucks as $truck)
                    <article class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm transition hover:border-indigo-200 hover:shadow-md dark:border-slate-800/70 dark:bg-slate-900/70">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ $truck->plate_number }} · {{ $truck->brand }} {{ $truck->model }}</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __($truck->status) }} · {{ number_format($truck->mileage) }} km</p>
                            </div>
                            <span @class([
                                'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold',
                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200' => $truck->alert_level === 'ok',
                                'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200' => $truck->alert_level === 'warning',
                                'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200' => $truck->alert_level === 'danger',
                            ])>
                                @switch($truck->alert_level)
                                    @case('danger') Requiere mantenimiento inmediato @break
                                    @case('warning') Mantenimiento próximo @break
                                    @default Al día
                                @endswitch
                            </span>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-4 text-xs text-slate-500 dark:text-slate-400">
                            <div>
                                <p class="font-semibold text-slate-600 dark:text-slate-300">Próximo mantenimiento</p>
                                <p>{{ optional($truck->next_maintenance)->format('d/m/Y') ?? 'No programado' }}</p>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-600 dark:text-slate-300">Asignaciones activas</p>
                                <p>{{ $truck->active_assignments_count }}</p>
                            </div>
                        </div>
                        @if($truck->document_alerts->isNotEmpty())
                            <div class="mt-3 rounded-xl border border-amber-200/60 bg-amber-50/70 p-3 text-xs text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                                <p class="font-semibold">Documentos por atender:</p>
                                <ul class="mt-1 list-disc space-y-1 pl-4">
                                    @foreach($truck->document_alerts as $document)
                                        <li>{{ $document->type_label }} · {{ optional($document->expires_at)->format('d/m/Y') ?? 'Sin fecha' }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200/70 bg-white/60 p-6 text-center text-sm text-slate-500 dark:border-slate-800/70 dark:bg-slate-900/40 dark:text-slate-400">
                        No se encontraron camiones con los filtros actuales.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="space-y-4">
            <header class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Choferes</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Controla licencias y capacitaciones vigentes.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <select wire:model.live="driverStatus" class="form-control min-w-[160px]">
                        <option value="">Todos</option>
                        @foreach ($driverStates as $key => $meta)
                            <option value="{{ $key }}">{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                    <input type="search" wire:model.live="driverSearch" placeholder="Buscar nombre o licencia" class="form-control min-w-[200px]">
                </div>
            </header>

            <div class="space-y-3">
                @forelse ($drivers as $driver)
                    <article class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm transition hover:border-indigo-200 hover:shadow-md dark:border-slate-800/70 dark:bg-slate-900/70">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ $driver->full_name }}</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Licencia {{ $driver->license_number }} · {{ optional($driver->license_expiration)->format('d/m/Y') }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-900/5 text-slate-700 dark:bg-white/10 dark:text-slate-200">
                                {{ __($driver->status) }}
                            </span>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-4 text-xs text-slate-500 dark:text-slate-400">
                            <div>
                                <p class="font-semibold text-slate-600 dark:text-slate-300">Capacitaciones vigentes</p>
                                <p>{{ $driver->valid_trainings->count() }}</p>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-600 dark:text-slate-300">Próxima asignación</p>
                                <p>
                                    @if ($driver->next_assignment)
                                        {{ optional($driver->next_assignment->start_date)->format('d/m/Y H:i') }} · {{ $driver->next_assignment->description }}
                                    @else
                                        Sin asignación
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if($driver->document_alerts->isNotEmpty())
                            <div class="mt-3 rounded-xl border border-amber-200/60 bg-amber-50/70 p-3 text-xs text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                                <p class="font-semibold">Alertas de documentación:</p>
                                <ul class="mt-1 list-disc space-y-1 pl-4">
                                    @foreach($driver->document_alerts as $document)
                                        <li>{{ $document->type_label }} · {{ optional($document->expires_at)->format('d/m/Y') ?? 'Sin fecha' }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200/70 bg-white/60 p-6 text-center text-sm text-slate-500 dark:border-slate-800/70 dark:bg-slate-900/40 dark:text-slate-400">
                        No se encontraron choferes con los filtros actuales.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
