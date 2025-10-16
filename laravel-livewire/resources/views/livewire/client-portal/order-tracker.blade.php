<div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <div>
 <h1 class="text-2xl font-semibold text-slate-900 ">{{ __('Mis envíos') }}</h1>
 <p class="text-sm text-slate-600 ">{{ __('Consulta el estado de tus pedidos y ajusta la ventana de entrega cuando sea necesario.') }}</p>
 </div>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
        {{ __('Regresar al panel') }}
    </a>
 </div>

 @if (session()->has('message'))
 <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm ">
 {{ session('message') }}
 </div>
 @endif

 <div class="space-y-6">
 @forelse ($orders as $order)
 <article class="surface-card space-y-4 p-6 shadow-lg">
 <header class="flex flex-wrap items-center justify-between gap-3">
 <div>
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Pedido :reference', ['reference' => $order->reference]) }}</h2>
 <p class="text-sm text-slate-500 ">{{ optional($order->client)->business_name ?? optional($order->client)->contact_name }}</p>
 </div>
 <span class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-4 py-1 text-xs font-semibold uppercase tracking-wide text-sky-700 ">
 {{ __($order->status) }}
 </span>
 </header>

 <div class="grid gap-4 sm:grid-cols-2">
 <dl class="space-y-1 text-sm text-slate-600 ">
 <div>
 <dt class="font-medium text-slate-500 ">{{ __('Origen') }}</dt>
 <dd>{{ $order->origin }}</dd>
 @if ($order->origin_latitude && $order->origin_longitude)
 <dd class="text-xs text-slate-500 ">{{ $order->origin_latitude }}, {{ $order->origin_longitude }}</dd>
 @endif
 </div>
 <div>
 <dt class="font-medium text-slate-500 ">{{ __('Destino') }}</dt>
 <dd>{{ $order->destination }}</dd>
 @if ($order->destination_latitude && $order->destination_longitude)
 <dd class="text-xs text-slate-500 ">{{ $order->destination_latitude }}, {{ $order->destination_longitude }}</dd>
 @endif
 </div>
 <div>
 <dt class="font-medium text-slate-500 ">{{ __('Peso / Volumen') }}</dt>
 <dd>{{ $order->cargo_weight_kg ? $order->cargo_weight_kg.' kg' : '—' }} · {{ $order->cargo_volume_m3 ? $order->cargo_volume_m3.' m³' : '—' }}</dd>
 </div>
 <div>
 <dt class="font-medium text-slate-500 ">{{ __('Costo estimado') }}</dt>
 <dd>{{ $order->estimated_cost ? \App\Support\Formatters\MoneyFormatter::pen($order->estimated_cost) : '—' }}</dd>
 </div>
 </dl>


 <dl class="space-y-1 text-sm text-slate-600 ">
 <div>
 <dt class="font-medium text-slate-500 ">{{ __('Recolección') }}</dt>
 <dd>{{ optional($order->pickup_date)?->format('d/m/Y H:i') ?? '—' }}</dd>
 </div>
 <div>
 <dt class="font-medium text-slate-500 ">{{ __('Entrega estimada') }}</dt>
 <dd>{{ optional($order->delivery_date)?->format('d/m/Y H:i') ?? '—' }}</dd>
 </div>
 <div>
 <dt class="font-medium text-slate-500 ">{{ __('Ventana de entrega') }}</dt>
 <dd>{{ optional($order->delivery_window_start)?->format('d/m/Y H:i') ?? '—' }} – {{ optional($order->delivery_window_end)?->format('d/m/Y H:i') ?? '—' }}</dd>
 </div>
 <div>
 <dt class="font-medium text-slate-500 ">{{ __('Última actualización') }}</dt>
 <dd>{{ optional($order->updated_at)?->diffForHumans() }}</dd>
 </div>
 </dl>
 </div>

 @php($plan = $order->routePlans->first())
 @if ($plan && $plan->map_url)
 <div class="overflow-hidden rounded-2xl border border-slate-200 ">
 <iframe src="{{ $plan->map_url }}" width="100%" height="280" style="border:0;" allowfullscreen loading="lazy"></iframe>
 </div>
 @endif

 @if ($order->assignments->isNotEmpty())
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-slate-200 text-sm ">
 <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 ">
 <tr>
 <th class="px-4 py-3">{{ __('Vehículo') }}</th>
 <th class="px-4 py-3">{{ __('Conductor') }}</th>
 <th class="px-4 py-3">{{ __('Estado') }}</th>
 <th class="px-4 py-3">{{ __('Inicio') }}</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-slate-100 bg-white ">
 @foreach ($order->assignments as $assignment)
 <tr>
 <td class="px-4 py-3 font-medium text-slate-900 ">{{ optional($assignment->truck)->plate_number ?? '—' }}</td>
 <td class="px-4 py-3 text-slate-600 ">{{ optional($assignment->driver)->full_name ?? optional($assignment->driver)->name ?? '—' }}</td>
 <td class="px-4 py-3 text-slate-600 ">{{ __($assignment->status) }}</td>
 <td class="px-4 py-3 text-slate-600 ">{{ optional($assignment->start_date)?->format('d/m/Y H:i') ?? '—' }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 @endif

 <form wire:submit.prevent="updateWindow({{ $order->id }})" class="grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 ">
 <h3 class="text-sm font-semibold text-slate-700 ">{{ __('Actualizar ventana de entrega') }}</h3>
 <div class="grid gap-4 sm:grid-cols-2">
 <div>
 <label class="form-label" for="window-start-{{ $order->id }}">{{ __('Inicio') }}</label>
 <input id="window-start-{{ $order->id }}" type="datetime-local" class="form-control" wire:model.defer="windowUpdates.{{ $order->id }}.delivery_window_start">
 @error('windowUpdates.' . $order->id . '.delivery_window_start') <span class="text-xs font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="form-label" for="window-end-{{ $order->id }}">{{ __('Fin') }}</label>
 <input id="window-end-{{ $order->id }}" type="datetime-local" class="form-control" wire:model.defer="windowUpdates.{{ $order->id }}.delivery_window_end">
 @error('windowUpdates.' . $order->id . '.delivery_window_end') <span class="text-xs font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 </div>
 <div>
 <label class="form-label" for="window-notes-{{ $order->id }}">{{ __('Comentarios adicionales') }}</label>
 <textarea id="window-notes-{{ $order->id }}" rows="2" class="form-control" wire:model.defer="windowUpdates.{{ $order->id }}.notes"></textarea>
 @error('windowUpdates.' . $order->id . '.notes') <span class="text-xs font-medium text-rose-500">{{ $message }}</span> @enderror
 </div>
 <div class="flex justify-end">
        <button type="submit" class="btn btn-primary">
        {{ __('Guardar cambios') }}
        </button>
 </div>
 </form>
 </article>
 @empty
 <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-500 shadow-sm ">
 {{ __('No se encontraron pedidos asociados a tu cuenta.') }}
 </div>
 @endforelse
 </div>
</div>
