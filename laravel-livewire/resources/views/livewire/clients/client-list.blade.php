<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Clientes</h1>
        <a href="{{ route('clients.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Nuevo Cliente</a>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white shadow rounded">
        <div class="p-4 flex flex-col md:flex-row gap-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por razon social, RUC o contacto..." class="w-full md:flex-1 px-3 py-2 border rounded">
            <div class="text-sm text-gray-500 self-center">Total: {{ $clients->total() }}</div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razon social</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RUC</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($clients as $client)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $client->business_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $client->tax_id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div>{{ $client->contact_name ?: 'Sin contacto' }}</div>
                                <div class="text-xs text-gray-400">{{ $client->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $client->phone ?: '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($client->notes, 60) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                <a href="{{ route('clients.edit', $client->id) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                <button wire:click="deleteClient({{ $client->id }})" wire:confirm="Eliminar este cliente?" class="text-red-600 hover:text-red-900">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No se encontraron clientes</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 bg-white border-t border-gray-200">
            {{ $clients->links() }}
        </div>
    </div>
</div>
