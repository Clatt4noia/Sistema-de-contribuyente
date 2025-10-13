<div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h1 class="text-2xl font-semibold text-slate-900 ">Clientes</h1>

 <a
 href="{{ route('clients.create') }}"
 class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 "
 >
 Nuevo Cliente
 </a>
 </div>

 @if (session()->has('message'))
 <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm ">
 <p>{{ session('message') }}</p>
 </div>
 @endif

 @if (session()->has('error'))
 <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm ">
 <p>{{ session('error') }}</p>
 </div>
 @endif

 <div class="surface-card overflow-hidden">
 <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 md:flex-row md:items-center">
 <input
 type="text"
 wire:model.live.debounce.300ms="search"
 placeholder="Buscar por razón social, RUC o contacto..."
 class="form-control md:flex-1"
 >
 <div class="text-sm font-medium text-slate-500 ">Total: {{ $clients->total() }}</div>
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
 <tr class="transition hover:bg-slate-50 ">
 <td class="px-6 py-4 text-sm font-semibold text-slate-900 ">
 {{ $client->business_name }}
 </td>
 <td class="px-6 py-4 text-sm text-slate-600 ">{{ $client->tax_id }}</td>
 <td class="px-6 py-4 text-sm text-slate-600 ">
 <div>{{ $client->contact_name ?: 'Sin contacto' }}</div>
 <div class="text-xs text-slate-400 ">{{ $client->email }}</div>
 </td>
 <td class="px-6 py-4 text-sm text-slate-600 ">{{ $client->phone ?: '-' }}</td>
 <td class="px-6 py-4 text-sm text-slate-600 ">
 {{ \Illuminate\Support\Str::limit($client->notes, 60) }}
 </td>
 <td class="px-6 py-4 text-sm font-semibold text-slate-600 ">
 <div class="flex flex-wrap items-center gap-3">
 <a
 href="{{ route('clients.edit', $client->id) }}"
 class="text-indigo-600 transition hover:text-indigo-700 "
 >
 Editar
 </a>
 <button
 type="button"
 wire:click="deleteClient({{ $client->id }})"
 wire:confirm="Eliminar este cliente?"
 class="text-rose-600 transition hover:text-rose-700 "
 >
 Eliminar
 </button>
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="6" class="px-6 py-8 text-center text-sm font-medium text-slate-500 ">
 No se encontraron clientes
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 <div class="border-t border-slate-200 bg-slate-50 px-6 py-4 ">
 {{ $clients->links() }}
 </div>
 </div>
</div>
