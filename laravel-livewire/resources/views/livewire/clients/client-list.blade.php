<div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Clientes</h1>

        <a
            href="{{ route('clients.create') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:bg-indigo-400 dark:text-slate-900 dark:hover:bg-indigo-300"
        >
            Nuevo Cliente
        </a>
    </div>

    @if (session()->has('message'))
        <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-700 shadow-sm dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-2xl border border-rose-200/80 bg-rose-50/80 px-4 py-3 text-sm text-rose-700 shadow-sm dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="surface-card overflow-hidden">
        <div class="flex flex-col gap-4 border-b border-slate-200/70 px-6 py-5 dark:border-slate-800/60 md:flex-row md:items-center">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar por razón social, RUC o contacto..."
                class="form-control md:flex-1"
            >
            <div class="text-sm font-medium text-slate-500 dark:text-slate-300">Total: {{ $clients->total() }}</div>
        </div>

        <div class="overflow-x-auto">
            <table class="surface-table">
                <thead>
                    <tr>
                        <th class="px-6 py-3">Razón social</th>
                        <th class="px-6 py-3">RUC</th>
                        <th class="px-6 py-3">Contacto</th>
                        <th class="px-6 py-3">Teléfono</th>
                        <th class="px-6 py-3">Notas</th>
                        <th class="px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr class="transition hover:bg-slate-50/60 dark:hover:bg-slate-900/40">
                            <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-slate-100">
                                {{ $client->business_name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $client->tax_id }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                <div>{{ $client->contact_name ?: 'Sin contacto' }}</div>
                                <div class="text-xs text-slate-400 dark:text-slate-500">{{ $client->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $client->phone ?: '-' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                {{ \Illuminate\Support\Str::limit($client->notes, 60) }}
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-600 dark:text-slate-300">
                                <div class="flex flex-wrap items-center gap-3">
                                    <a
                                        href="{{ route('clients.edit', $client->id) }}"
                                        class="text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-300 dark:hover:text-indigo-200"
                                    >
                                        Editar
                                    </a>
                                    <button
                                        type="button"
                                        wire:click="deleteClient({{ $client->id }})"
                                        wire:confirm="Eliminar este cliente?"
                                        class="text-rose-600 transition hover:text-rose-700 dark:text-rose-300 dark:hover:text-rose-200"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm font-medium text-slate-500 dark:text-slate-300">
                                No se encontraron clientes
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200/70 bg-slate-50/70 px-6 py-4 dark:border-slate-800/60 dark:bg-slate-900/40">
            {{ $clients->links() }}
        </div>
    </div>
</div>
