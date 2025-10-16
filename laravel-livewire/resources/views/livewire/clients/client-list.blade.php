<div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h1 class="text-2xl font-semibold text-slate-900 ">Clientes</h1>

    <a
        href="{{ route('clients.create') }}"
        class="btn btn-primary"
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
    <table class="table table-md">
      <thead>
        <tr class="table-row">
          <th class="table-header">Razón social</th>
          <th class="table-header">RUC</th>
          <th class="table-header">Contacto</th>
          <th class="table-header">Teléfono</th>
          <th class="table-header">Notas</th>
          <th class="table-header">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($clients as $client)
          <tr class="table-row table-row-hover">
            <td class="table-cell text-sm font-semibold text-slate-900 ">
              {{ $client->business_name }}
            </td>
            <td class="table-cell text-sm text-slate-600 ">{{ $client->tax_id }}</td>
            <td class="table-cell text-sm text-slate-600 ">
              <div>{{ $client->contact_name ?: 'Sin contacto' }}</div>
              <div class="text-xs text-slate-400 ">{{ $client->email }}</div>
            </td>
            <td class="table-cell text-sm text-slate-600 ">{{ $client->phone ?: '-' }}</td>
            <td class="table-cell text-sm text-slate-600 ">
              {{ \Illuminate\Support\Str::limit($client->notes, 60) }}
            </td>
            <td class="table-cell text-sm font-semibold text-slate-600 ">
              <div class="flex flex-wrap items-center gap-3">
                <a
                  href="{{ route('clients.edit', $client->id) }}"
                  class="btn btn-ghost btn-sm"
                >
                  Editar
                </a>
                <button
                  type="button"
                  wire:click="deleteClient({{ $client->id }})"
                  wire:confirm="Eliminar este cliente?"
                  class="btn btn-danger btn-sm"
                >
                  Eliminar
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr class="table-row">
            <td colspan="6" class="table-empty">
              No se encontraron clientes
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="table-footer bg-slate-50">
      {{ $clients->links() }}
    </div>
 </div>
</div>
