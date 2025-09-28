<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Rutas adicionales</h2>
        @if (session()->has('message'))
            <span class="text-sm text-green-600">{{ session('message') }}</span>
        @endif
    </div>

    <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Planificador</label>
            <input type="text" wire:model.defer="planner" class="w-full px-3 py-2 border rounded @error('planner') border-red-500 @enderror" placeholder="Operador a cargo">
            @error('planner') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">URL del mapa</label>
            <input type="url" wire:model.defer="map_url" class="w-full px-3 py-2 border rounded @error('map_url') border-red-500 @enderror" placeholder="https://maps...">
            @error('map_url') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Resumen *</label>
            <textarea rows="3" wire:model.defer="route_summary" class="w-full px-3 py-2 border rounded @error('route_summary') border-red-500 @enderror" placeholder="Puntos clave, carreteras, riesgos..."></textarea>
            @error('route_summary') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Datos JSON</label>
            <textarea rows="3" wire:model.defer="route_data" class="w-full px-3 py-2 border rounded @error('route_data') border-red-500 @enderror" placeholder='{"waypoints": []}'></textarea>
            @error('route_data') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="md:col-span-2 flex justify-end">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Agregar ruta</button>
        </div>
    </form>

    <div class="bg-white border border-gray-200 rounded">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Planificador</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Resumen</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Mapa</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($plans as $plan)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $plan->planner ?: 'No definido' }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ \Illuminate\Support\Str::limit($plan->route_summary, 80) }}</td>
                        <td class="px-4 py-2 text-sm text-blue-600">
                            @if($plan->map_url)
                                <a href="{{ $plan->map_url }}" target="_blank" class="hover:underline">Ver mapa</a>
                            @else
                                <span class="text-gray-400">Sin enlace</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm">
                            <button wire:click="delete({{ $plan->id }})" wire:confirm="Eliminar esta ruta?" class="text-red-600 hover:text-red-800">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-center text-gray-500">No hay rutas adicionales registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
