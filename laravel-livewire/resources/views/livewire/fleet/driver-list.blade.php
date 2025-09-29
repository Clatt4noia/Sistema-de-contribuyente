<div class="container mx-auto py-6 space-y-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Gestion de Choferes</h1>
        <a href="{{ route('fleet.drivers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            Agregar Chofer
        </a>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-4 flex flex-col md:flex-row justify-between space-y-4 md:space-y-0">
            <div class="flex-1 max-w-md">
                <input type="text" wire:model.live="search" placeholder="Buscar por nombre, documento o licencia..." class="w-full px-3 py-2 border rounded-md">
            </div>
            <div class="flex-none">
                <select wire:model.live="status" class="px-3 py-2 border rounded-md">
                    <option value="">Todos los estados</option>
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                    <option value="on_leave">De permiso</option>
                    <option value="assigned">Asignado</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Licencia</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimiento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horarios</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evaluacion</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($drivers as $driver)
                        @php
                            $statusStyles = [
                                'active' => ['label' => 'Activo', 'class' => 'bg-green-100 text-green-800'],
                                'inactive' => ['label' => 'Inactivo', 'class' => 'bg-red-100 text-red-800'],
                                'on_leave' => ['label' => 'De permiso', 'class' => 'bg-yellow-100 text-yellow-800'],
                                'assigned' => ['label' => 'Asignado', 'class' => 'bg-blue-100 text-blue-800'],
                            ];
                            $statusConfig = $statusStyles[$driver->status] ?? $statusStyles['active'];
                            $scheduleSummary = $driver->schedules->map(fn ($schedule) => substr($schedule->day_of_week, 0, 3) . ' ' . ($schedule->start_time?->format('H:i') ?? '') . '-' . ($schedule->end_time?->format('H:i') ?? ''))->filter()->implode(', ');
                            $averageScore = $driver->evaluations->isNotEmpty() ? round($driver->evaluations->avg('score'), 2) : null;
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $driver->full_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $driver->document_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $driver->license_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $driver->license_expiration->format('d/m/Y') }}
                                @if($driver->license_expiration->isPast())
                                    <span class="text-red-600 font-bold ml-2">VENCIDA</span>
                                @elseif($driver->license_expiration->diffInDays(now()) < 30)
                                    <span class="text-yellow-600 font-bold ml-2">PROXIMA A VENCER</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $scheduleSummary ?: 'Sin horarios' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $averageScore ? $averageScore . ' / 5' : 'Sin evaluaciones' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusConfig['class'] }}">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('fleet.drivers.edit', $driver) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</a>
                                <button wire:click="deleteDriver({{ $driver->id }})" wire:confirm="Esta seguro de eliminar este chofer?" class="text-red-600 hover:text-red-900">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">No se encontraron choferes</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 bg-white border-t border-gray-200">
            {{ $drivers->links() }}
        </div>
    </div>
</div>
