<div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <div>
 <h1 class="text-2xl font-semibold text-token ">{{ __('Mis envíos') }}</h1>
 <p class="text-sm text-token ">{{ __('Consulta el estado de tus Ordenes y ajusta la ventana de entrega cuando sea necesario.') }}</p>
 </div>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
        {{ __('Regresar al panel') }}
    </a>
 </div>

 @if (session()->has('message'))
 <div class="alert alert-success">
 {{ session('message') }}
 </div>
 @endif

 <div class="space-y-6">
 @forelse ($orders as $order)
 <article class="surface-card space-y-4 p-6 shadow-lg">
 <header class="flex flex-wrap items-center justify-between gap-3">
 <div>
 <h2 class="text-lg font-semibold text-token ">{{ __('Orden :reference', ['reference' => $order->reference]) }}</h2>
 <p class="text-sm text-token ">{{ optional($order->client)->business_name ?? optional($order->client)->contact_name }}</p>
 </div>
 <span class="badge badge-accent">
 {{ $order->status->label() }}
 </span>
 </header>

 <div class="grid gap-4 sm:grid-cols-2">
 <dl class="space-y-1 text-sm text-token ">
 <div>
 <dt class="font-medium text-token ">{{ __('Origen') }}</dt>
 <dd>{{ $order->origin }}</dd>
 @if ($order->origin_latitude && $order->origin_longitude)
 <dd class="text-xs text-token ">{{ $order->origin_latitude }}, {{ $order->origin_longitude }}</dd>
 @endif
 </div>
 <div>
 <dt class="font-medium text-token ">{{ __('Destino') }}</dt>
 <dd>{{ $order->destination }}</dd>
 @if ($order->destination_latitude && $order->destination_longitude)
 <dd class="text-xs text-token ">{{ $order->destination_latitude }}, {{ $order->destination_longitude }}</dd>
 @endif
 </div>
 <div>
 <dt class="font-medium text-token ">{{ __('Peso / Volumen') }}</dt>
 <dd>{{ $order->cargo_weight_kg ? $order->cargo_weight_kg.' kg' : '—' }} · {{ $order->cargo_volume_m3 ? $order->cargo_volume_m3.' m³' : '—' }}</dd>
 </div>
 <div>
 <dt class="font-medium text-token ">{{ __('Costo estimado') }}</dt>
 <dd>{{ $order->estimated_cost ? \App\Support\Formatters\MoneyFormatter::pen($order->estimated_cost) : '—' }}</dd>
 </div>
 </dl>


 <dl class="space-y-1 text-sm text-token ">
 <div>
 <dt class="font-medium text-token ">{{ __('Recolección') }}</dt>
 <dd>{{ optional($order->pickup_date)?->format('d/m/Y H:i') ?? '—' }}</dd>
 </div>
 <div>
 <dt class="font-medium text-token ">{{ __('Entrega estimada') }}</dt>
 <dd>{{ optional($order->delivery_date)?->format('d/m/Y H:i') ?? '—' }}</dd>
 </div>
 <div>
 <dt class="font-medium text-token ">{{ __('Ventana de entrega') }}</dt>
 <dd>{{ optional($order->delivery_window_start)?->format('d/m/Y H:i') ?? '—' }} – {{ optional($order->delivery_window_end)?->format('d/m/Y H:i') ?? '—' }}</dd>
 </div>
 <div>
 <dt class="font-medium text-token ">{{ __('Última actualización') }}</dt>
 <dd>{{ optional($order->updated_at)?->diffForHumans() }}</dd>
 </div>
 </dl>
 </div>

 @php($plan = $order->routePlans->first())
 @if ($plan && $plan->map_url)
 <div class="overflow-hidden rounded-2xl border border-token ">
 <iframe src="{{ $plan->map_url }}" width="100%" height="280" style="border:0;" allowfullscreen loading="lazy"></iframe>
 </div>
 @endif

 @if ($order->assignments->isNotEmpty())
    <div class="overflow-x-auto">
      <table class="table table-sm text-sm">
        <thead>
          <tr class="table-row">
            <th class="table-header">{{ __('Vehículo') }}</th>
            <th class="table-header">{{ __('Conductor') }}</th>
            <th class="table-header">{{ __('Estado') }}</th>
            <th class="table-header">{{ __('Inicio') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($order->assignments as $assignment)
            <tr class="table-row table-row-hover">
              <td class="table-cell font-medium text-token ">{{ optional($assignment->truck)->plate_number ?? '—' }}</td>
              <td class="table-cell text-token ">{{ optional($assignment->driver)->full_name ?? optional($assignment->driver)->name ?? '—' }}</td>
              <td class="table-cell text-token ">{{ $assignment->status->label() }}</td>
              <td class="table-cell text-token ">{{ optional($assignment->start_date)?->format('d/m/Y H:i') ?? '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
 @endif

 <form wire:submit.prevent="updateWindow({{ $order->id }})" class="grid gap-4 rounded-2xl border border-token bg-surface p-4 ">
 <h3 class="text-sm font-semibold text-token ">{{ __('Actualizar ventana de entrega') }}</h3>
 <div class="grid gap-4 sm:grid-cols-2">
 <div>
 <label class="form-label" for="window-start-{{ $order->id }}">{{ __('Inicio') }}</label>
 <input id="window-start-{{ $order->id }}" type="datetime-local" class="form-control" wire:model.defer="windowUpdates.{{ $order->id }}.delivery_window_start">
 @error('windowUpdates.' . $order->id . '.delivery_window_start') <span class="form-error">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="form-label" for="window-end-{{ $order->id }}">{{ __('Fin') }}</label>
 <input id="window-end-{{ $order->id }}" type="datetime-local" class="form-control" wire:model.defer="windowUpdates.{{ $order->id }}.delivery_window_end">
 @error('windowUpdates.' . $order->id . '.delivery_window_end') <span class="form-error">{{ $message }}</span> @enderror
 </div>
 </div>
 <div>
 <label class="form-label" for="window-notes-{{ $order->id }}">{{ __('Comentarios adicionales') }}</label>
 <textarea id="window-notes-{{ $order->id }}" rows="2" class="form-control" wire:model.defer="windowUpdates.{{ $order->id }}.notes"></textarea>
 @error('windowUpdates.' . $order->id . '.notes') <span class="form-error">{{ $message }}</span> @enderror
 </div>
 <div class="flex justify-end">
        <button type="submit" class="btn btn-primary">
        {{ __('Guardar cambios') }}
        </button>
 </div>
 </form>
 </article>
 @empty
    <div class="rounded-2xl border border-token bg-surface p-6 text-center text-sm text-token shadow-sm ">
 {{ __('No se encontraron Ordenes asociados a tu cuenta.') }}
 </div>
 @endforelse
 </div>
</div>
