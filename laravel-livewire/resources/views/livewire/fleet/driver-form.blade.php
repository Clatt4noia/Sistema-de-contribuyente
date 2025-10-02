<div class="container mx-auto py-6 space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Editar Chofer' : 'Registrar Chofer' }}</h1>
            @if ($form['license_expiration'])
                <p class="mt-2 text-sm">
                    <span class="font-medium">Vigencia de licencia:</span>
                    @php
                        $expiresAt = \Illuminate\Support\Carbon::parse($form['license_expiration']);
                        $daysLeft = now()->diffInDays($expiresAt, false);
                    @endphp
                    <span @class([
                        'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold',
                        'bg-red-100 text-red-700' => $daysLeft < 0,
                        'bg-yellow-100 text-yellow-700' => $daysLeft >= 0 && $daysLeft <= 30,
                        'bg-green-100 text-green-700' => $daysLeft > 30,
                    ])>
                        {{ $expiresAt->format('d/m/Y') }}
                        ({{ $daysLeft < 0 ? 'Vencida' : 'Quedan ' . $daysLeft . ' días' }})
                    </span>
                </p>
            @endif
        </div>
        <a href="{{ route('fleet.drivers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            Volver
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6 space-y-8">
        <form wire:submit.prevent="save" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" id="name" wire:model.defer="form.name" class="w-full px-3 py-2 border rounded-md">
                    @error('form.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                    <input type="text" id="last_name" wire:model.defer="form.last_name" class="w-full px-3 py-2 border rounded-md">
                    @error('form.last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="document_number" class="block text-sm font-medium text-gray-700 mb-1">Numero de documento</label>
                    <input type="text" id="document_number" wire:model.defer="form.document_number" class="w-full px-3 py-2 border rounded-md">
                    @error('form.document_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="license_number" class="block text-sm font-medium text-gray-700 mb-1">Numero de licencia</label>
                    <input type="text" id="license_number" wire:model.defer="form.license_number" class="w-full px-3 py-2 border rounded-md">
                    @error('form.license_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="license_expiration" class="block text-sm font-medium text-gray-700 mb-1">Vencimiento de licencia</label>
                    <input type="date" id="license_expiration" wire:model.defer="form.license_expiration" class="w-full px-3 py-2 border rounded-md">
                    @error('form.license_expiration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                    <input type="text" id="phone" wire:model.defer="form.phone" class="w-full px-3 py-2 border rounded-md">
                    @error('form.phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" wire:model.defer="form.email" class="w-full px-3 py-2 border rounded-md">
                    @error('form.email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select id="status" wire:model.defer="form.status" class="w-full px-3 py-2 border rounded-md">
                        <option value="active">Activo</option>
                        <option value="inactive">Inactivo</option>
                        <option value="on_leave">De permiso</option>
                        <option value="assigned">Asignado</option>
                    </select>
                    @error('form.status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Direccion</label>
                <input type="text" id="address" wire:model.defer="form.address" class="w-full px-3 py-2 border rounded-md">
                @error('form.address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                <textarea id="notes" wire:model.defer="form.notes" rows="4" class="w-full px-3 py-2 border rounded-md"></textarea>
                @error('form.notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="border-t pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Horarios</h2>
                    <button type="button" wire:click="addSchedule" class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Agregar horario</button>
                </div>

                @error('schedules')
                    <div class="text-red-600 text-sm mb-2">{{ $message }}</div>
                @enderror

                <div class="space-y-4">
                    @forelse ($schedules as $index => $schedule)
                        <div wire:key="schedule-{{ $index }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end bg-gray-50 p-4 rounded">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Dia</label>
                                <select wire:model.defer="schedules.{{ $index }}.day_of_week" class="w-full px-3 py-2 border rounded-md">
                                    <option value="Lunes">Lunes</option>
                                    <option value="Martes">Martes</option>
                                    <option value="Miercoles">Miercoles</option>
                                    <option value="Jueves">Jueves</option>
                                    <option value="Viernes">Viernes</option>
                                    <option value="Sabado">Sabado</option>
                                    <option value="Domingo">Domingo</option>
                                </select>
                                @error('schedules.' . $index . '.day_of_week') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Inicio</label>
                                <input type="time" wire:model.defer="schedules.{{ $index }}.start_time" class="w-full px-3 py-2 border rounded-md">
                                @error('schedules.' . $index . '.start_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Fin</label>
                                <input type="time" wire:model.defer="schedules.{{ $index }}.end_time" class="w-full px-3 py-2 border rounded-md">
                                @error('schedules.' . $index . '.end_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex justify-end">
                                <button type="button" wire:click="removeSchedule({{ $index }})" class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700">Eliminar</button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No se han definido horarios. Agrega al menos uno para planificar disponibilidad.</p>
                    @endforelse
                </div>
            </div>

            <div class="border-t pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Capacitaciones</h2>
                    <button type="button" wire:click="addTraining" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Agregar capacitación</button>
                </div>

                <div class="space-y-4">
                    @forelse ($trainings as $index => $training)
                        <div wire:key="training-{{ $index }}" class="grid grid-cols-1 md:grid-cols-6 gap-4 bg-indigo-50/70 p-4 rounded">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                                <input type="text" wire:model.defer="trainings.{{ $index }}.name" class="w-full px-3 py-2 border rounded-md">
                                @error('trainings.' . $index . '.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Proveedor</label>
                                <input type="text" wire:model.defer="trainings.{{ $index }}.provider" class="w-full px-3 py-2 border rounded-md">
                                @error('trainings.' . $index . '.provider') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Emitida</label>
                                <input type="date" wire:model.defer="trainings.{{ $index }}.issued_at" class="w-full px-3 py-2 border rounded-md">
                                @error('trainings.' . $index . '.issued_at') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Vence</label>
                                <input type="date" wire:model.defer="trainings.{{ $index }}.expires_at" class="w-full px-3 py-2 border rounded-md">
                                @error('trainings.' . $index . '.expires_at') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Horas</label>
                                <input type="number" min="0" wire:model.defer="trainings.{{ $index }}.hours" class="w-full px-3 py-2 border rounded-md">
                                @error('trainings.' . $index . '.hours') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                                <select wire:model.defer="trainings.{{ $index }}.status" class="w-full px-3 py-2 border rounded-md">
                                    <option value="valid">Vigente</option>
                                    <option value="in_progress">En curso</option>
                                    <option value="expired">Vencida</option>
                                </select>
                                @error('trainings.' . $index . '.status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Certificado (URL)</label>
                                <input type="url" wire:model.defer="trainings.{{ $index }}.certificate_url" class="w-full px-3 py-2 border rounded-md">
                                @error('trainings.' . $index . '.certificate_url') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex justify-end md:col-span-6">
                                <button type="button" wire:click="removeTraining({{ $index }})" class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700">Eliminar</button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No se han registrado capacitaciones.</p>
                    @endforelse
                </div>
            </div>

            <div class="border-t pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Evaluaciones</h2>
                    <button type="button" wire:click="addEvaluation" class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Agregar evaluacion</button>
                </div>

                <div class="space-y-4">
                    @forelse ($evaluations as $index => $evaluation)
                        <div wire:key="evaluation-{{ $index }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end bg-gray-50 p-4 rounded">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Fecha</label>
                                <input type="date" wire:model.defer="evaluations.{{ $index }}.evaluated_at" class="w-full px-3 py-2 border rounded-md">
                                @error('evaluations.' . $index . '.evaluated_at') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Puntaje (1-5)</label>
                                <input type="number" min="1" max="5" wire:model.defer="evaluations.{{ $index }}.score" class="w-full px-3 py-2 border rounded-md">
                                @error('evaluations.' . $index . '.score') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Evaluador</label>
                                <input type="text" wire:model.defer="evaluations.{{ $index }}.evaluator" class="w-full px-3 py-2 border rounded-md">
                                @error('evaluations.' . $index . '.evaluator') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Comentarios</label>
                                <textarea wire:model.defer="evaluations.{{ $index }}.comments" rows="2" class="w-full px-3 py-2 border rounded-md"></textarea>
                                @error('evaluations.' . $index . '.comments') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex justify-end md:col-span-5">
                                <button type="button" wire:click="removeEvaluation({{ $index }})" class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700">Eliminar</button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No se han registrado evaluaciones.</p>
                    @endforelse
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    {{ $isEdit ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </form>
    </div>
</div>

