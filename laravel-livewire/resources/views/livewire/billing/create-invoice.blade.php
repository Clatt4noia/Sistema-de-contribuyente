<div class="space-y-6">
  <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
    <div>
      <h1 class="text-2xl font-semibold text-token">Emitir comprobante SUNAT</h1>
      <p class="text-sm text-token-muted">Complete los datos para generar la factura electrónica.</p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
      <button type="button" wire:click="$dispatch('open-client-modal')" class="btn btn-primary">
        <x-heroicon-o-user-plus class="h-4 w-4" />
        Nuevo cliente
      </button>

      <a href="{{ route('billing.invoices.index') }}" class="btn btn-secondary">
        Volver
      </a>
    </div>
  </div>

  <div class="grid gap-6 lg:grid-cols-[2fr,1fr]">
    <div class="space-y-6">
      {{-- DATOS DEL COMPROBANTE --}}
      <div class="surface-card space-y-6 p-6 shadow-lg">
        <h2 class="text-lg font-semibold text-token">Datos del comprobante</h2>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <div class="form-field">
            <label class="form-label">Tipo de comprobante</label>
            <select wire:model.live="documentType" class="form-control">
            @foreach($documentTypes as $type)
                <option value="{{ $type->code }}">{{ $type->sunat_name ?? $type->description }}</option>
            @endforeach
            </select>

            @error('documentType') <span class="form-error">{{ $message }}</span> @enderror
          </div>

          <div class="form-field">
            <label class="form-label">Tipo de operación</label>
            <select wire:model="operationType" class="form-control">
              @foreach($operationTypes as $operation)
                <option value="{{ $operation['code'] }}">{{ $operation['code'] }} - {{ $operation['label'] }}</option>
              @endforeach
            </select>
            @error('operationType') <span class="form-error">{{ $message }}</span> @enderror
          </div>

    <div class="form-field">
  <label class="form-label">Serie</label>

  <input
    type="text"
    wire:model.live="series"
    readonly
    class="form-control bg-surface-muted text-token-muted cursor-not-allowed"
  />

  @error('series') <span class="form-error">{{ $message }}</span> @enderror
</div>



          <div class="form-field">
            <label class="form-label">Correlativo</label>
            <input type="text" value="{{ $correlative }}" readonly
              class="form-control cursor-not-allowed bg-surface-muted text-token-muted" />
          </div>

          <div class="form-field">
            <label class="form-label">Moneda</label>
            <select wire:model="currency" class="form-control">
              <option value="PEN">Soles (PEN)</option>
              <option value="USD">Dólares (USD)</option>
            </select>
            @error('currency') <span class="form-error">{{ $message }}</span> @enderror
          </div>

          <div class="form-field">
            <label class="form-label">Fecha de emisión</label>
            <input type="date" wire:model="issueDate" class="form-control" />
            @error('issueDate') <span class="form-error">{{ $message }}</span> @enderror
          </div>

          <div class="form-field">
            <label class="form-label">Fecha de vencimiento</label>
            <input type="date" wire:model="dueDate" class="form-control" />
            @error('dueDate') <span class="form-error">{{ $message }}</span> @enderror
          </div>
        </div>
      </div>

      {{-- CLIENTE --}}
      <div class="surface-card space-y-6 p-6 shadow-lg">
        <h2 class="text-lg font-semibold text-token">Cliente</h2>

        <div class="space-y-3">
          <div class="relative">
            <input
              type="text"
              wire:model.live.debounce.300ms="clientSearch"
              placeholder="Buscar por RUC o razón social"
              class="form-control"
            />
          </div>

          @error('clientSearch') <span class="form-error">{{ $message }}</span> @enderror

          @if($selectedClient)
            <div class="rounded-xl border border-token bg-surface p-4 text-sm text-token">
              <div class="font-semibold text-token">{{ $selectedClient['name'] }}</div>
              <div class="text-xs uppercase tracking-wide text-token-muted">{{ $selectedClient['document'] }}</div>

              @if($selectedClient['billing_address'])
                <p class="mt-2 text-xs">{{ $selectedClient['billing_address'] }}</p>
              @endif

              <div class="mt-2 flex flex-wrap gap-4 text-xs">
                @if($selectedClient['email'])
                  <span class="flex items-center gap-1">
                    <x-heroicon-o-envelope class="h-4 w-4" /> {{ $selectedClient['email'] }}
                  </span>
                @endif

                @if($selectedClient['phone'])
                  <span class="flex items-center gap-1">
                    <x-heroicon-o-phone class="h-4 w-4" /> {{ $selectedClient['phone'] }}
                  </span>
                @endif
              </div>
            </div>
          @endif
        </div>
      </div>

      {{-- OrdenES A FACTURAR (BUSCADOR + RESULTADOS DENTRO) --}}
      <div class="surface-card space-y-6 p-6 shadow-lg">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
          <div>
            <h2 class="text-lg font-semibold text-token">Ordenes a facturar</h2>
            <p class="text-xs text-token-muted">Busca y agrega Ordenes pendientes del cliente seleccionado.</p>
          </div>

          <div class="grid w-full gap-4 md:w-auto md:grid-cols-[minmax(0,380px)_220px]">
            <div class="form-field">
              <label class="form-label">Buscar Orden</label>
              <input
                type="text"
                wire:model.live.debounce.300ms="orderSearch"
                placeholder="Referencia, origen o destino"
                class="form-control {{ $selectedClient ? '' : 'cursor-not-allowed opacity-60' }}"
                @disabled(!$selectedClient)
              />
            </div>

            <div class="form-field">
              <label class="form-label">Tipo de carga</label>
              <select
                wire:model="cargoTypeFilter"
                class="form-control {{ $selectedClient ? '' : 'cursor-not-allowed opacity-60' }}"
                @disabled(!$selectedClient)
              >
                <option value="">Todos los tipos</option>
                @foreach($cargoTypes as $type)
                  <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        {{-- Mensajes --}}
        @if(!$selectedClient)
          <p class="text-xs text-token">Seleccione un cliente para listar sus Ordenes pendientes de facturar.</p>
        @elseif(empty($orderResults) && ($orderSearch !== '' || $cargoTypeFilter))
          <p class="text-xs text-token">No se encontraron Ordenes pendientes que coincidan con los filtros aplicados.</p>
        @endif

       {{-- Resultados dentro de la tarjeta --}}
        @if($selectedClient && !empty($orderResults))
        <div class="rounded-xl border border-token bg-elevated">
            <div class="flex items-center justify-between px-4 py-3">
            <span class="text-sm font-semibold text-token">Resultados</span>

            <button
                type="button"
                class="text-xs text-token-muted hover:text-token"
                wire:click="$set('orderSearch','')"
            >
                Limpiar
            </button>
            </div>

            <div class="max-h-72 overflow-y-auto">
            <ul class="divide-y divide-token">
                @foreach($orderResults as $order)
                <li>
                    <button
                    type="button"
                    wire:click="addOrder({{ $order['id'] }})"
                    class="group w-full text-left px-4 py-4
                            transition
                            hover:bg-surface-muted/40
                            focus:outline-none focus-visible:ring-2 focus-visible:ring-accent/40
                            active:bg-surface-muted/60"
                    >
                    <div class="flex items-start justify-between gap-4">
                        {{-- IZQUIERDA --}}
                        <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-base font-semibold text-token">
                            Orden {{ $order['reference'] }}
                            </span>

                            @if($order['cargo_type'])
                            <span class="inline-flex items-center gap-1 rounded-full bg-surface-muted px-2 py-0.5
                                        text-[11px] font-semibold uppercase tracking-wide text-token">
                                {{ $order['cargo_type'] }}
                                @if($order['is_hazardous'])
                                <x-heroicon-o-exclamation-triangle class="h-3 w-3 text-warning" />
                                @endif
                            </span>
                            @endif

                            @if($order['status_label'])
                            <span class="inline-flex items-center rounded-full bg-surface-muted px-2 py-0.5
                                        text-[10px] font-semibold uppercase tracking-wide text-token">
                                {{ $order['status_label'] }}
                            </span>
                            @endif
                        </div>

                        <div class="mt-1 text-sm text-token">
                            @if($order['origin']) <span>Origen: {{ $order['origin'] }}</span> @endif
                            @if($order['destination'])
                            <span class="ml-3">Destino: {{ $order['destination'] }}</span>
                            @endif
                        </div>

                        @if($order['pickup_date'])
                            <div class="mt-1 text-xs text-token-muted">
                            Recojo: {{ $order['pickup_date'] }}
                            </div>
                        @endif

                        {{-- Hint de acción (solo aparece al hover/focus) --}}
                        <div class="mt-2 flex items-center gap-2 text-xs text-token-muted
                                    opacity-0 transition group-hover:opacity-100 group-focus-visible:opacity-100">
                            <span class="inline-flex items-center rounded-full bg-surface-muted px-2 py-0.5">
                            Click para seleccionar
                            </span>
                        </div>
                        </div>

                        {{-- DERECHA --}}
                        <div class="shrink-0 text-right">
                        <div class="text-base font-bold text-token">
                            {{ $this->currencySymbol }} {{ number_format($order['estimated_cost'], 2) }}
                        </div>

                        {{-- Indicador visual de selección (chevron) --}}
                        <div class="mt-2 inline-flex items-center gap-2 text-xs text-token-muted">
                            <span class="hidden sm:inline opacity-70 group-hover:opacity-100 transition">
                            Seleccionar
                            </span>
                            <span
                            class="inline-flex h-9 w-9 items-center justify-center rounded-full
                                    bg-surface-muted text-token
                                    transition
                                    group-hover:translate-x-0.5 group-hover:bg-surface
                                    group-active:scale-95"
                            aria-hidden="true"
                            >
                            ›
                            </span>
                        </div>
                        </div>
                    </div>
                    </button>
                </li>
                @endforeach
            </ul>
            </div>
        </div>
        @endif
        </div>


{{-- TABLA DE ÍTEMS (Ordenes AGREGADOS) --}}
<div class="overflow-hidden rounded-xl border border-token shadow-sm bg-white">
  <table class="w-full table-fixed border-separate border-spacing-0 !table">
    <thead class="bg-surface-muted !table-header-group">
      <tr class="!table-row">
        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-token-muted w-32 !table-cell">
          Cantidad
        </th>
        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-token-muted !table-cell">
          Descripción
        </th>
        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-token-muted w-32 !table-cell">
          Precio unit.
        </th>
        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-token-muted w-40 !table-cell">
          Base imponible
        </th>
        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-token-muted w-36 !table-cell">
          Acciones
        </th>
      </tr>
    </thead>

    <tbody class="divide-y divide-token/20 text-sm !table-row-group">
      @forelse($invoiceItems as $index => $item)
        <tr
          wire:key="item-{{ $item['order_id'] ?? $index }}"
          class="align-top hover:bg-surface-muted/40 transition !table-row"
        >
          <td class="px-4 py-3 align-top !table-cell">
            <div class="text-sm font-semibold text-token leading-tight">1 servicio</div>
            <div class="text-xs text-token-muted leading-tight">Servicio de transporte</div>
          </td>

          <td class="px-4 py-3 align-top !table-cell">
            <div class="font-medium text-token leading-snug">
              {{ $item['description'] }}
            </div>

            @if(!empty($item['reference'] ?? $item['sku'] ?? ''))
              <div class="mt-1 text-xs text-token-muted">
                {{ $item['reference'] ?? $item['sku'] ?? '' }}
              </div>
            @endif

            @if(!empty($item['cargo_type']))
              <div class="mt-2">
                <span class="inline-flex items-center rounded-full bg-surface-muted px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-token">
                  {{ $item['cargo_type'] }}
                </span>
              </div>
            @endif
          </td>

          <td class="px-4 py-3 align-top text-right whitespace-nowrap !table-cell">
            {{ $this->currencySymbol }} {{ number_format((float)($item['unit_price'] ?? 0), 2) }}
          </td>

          <td class="px-4 py-3 align-top text-right whitespace-nowrap !table-cell">
            {{ $this->currencySymbol }} {{ number_format((float)($item['taxable_amount'] ?? 0), 2) }}
          </td>

          <td class="px-4 py-3 align-top text-right !table-cell">
            <button
              type="button"
              wire:click="removeItem({{ $index }})"
              class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700 transition"
            >
              <x-heroicon-o-trash class="h-4 w-4" />
              Eliminar
            </button>
          </td>
        </tr>
      @empty
        <tr class="!table-row">
          <td colspan="5" class="px-4 py-10 text-center text-sm text-token-muted !table-cell">
            No se han agregado Ordenes. Utilice el buscador para añadir ítems.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>


      @error('invoiceItems') <span class="form-error">{{ $message }}</span> @enderror
    </div>

    {{-- COLUMNA DERECHA --}}
    <div class="space-y-6">
      <div class="surface-card space-y-4 p-6 shadow-lg">
        <h2 class="text-lg font-semibold text-token">Totales</h2>

        <dl class="space-y-3 text-sm">
          <div class="flex items-center justify-between">
            <dt class="text-token">Sub total</dt>
            <dd class="font-semibold text-token">{{ $this->currencySymbol }} {{ number_format($subtotal, 2) }}</dd>
          </div>

          <div class="flex items-center justify-between">
            <dt class="text-token">IGV ({{ rtrim(rtrim(number_format($this->taxRate, 2), '0'), '.') }}%)</dt>
            <dd class="font-semibold text-token">{{ $this->currencySymbol }} {{ number_format($igv, 2) }}</dd>
          </div>

          <div class="flex items-center justify-between text-base">
            <dt class="font-semibold text-token">Importe total</dt>
            <dd class="font-bold text-accent">{{ $this->currencySymbol }} {{ number_format($total, 2) }}</dd>
          </div>
        </dl>
      </div>

      <div class="surface-card p-6 shadow-lg">
        <button type="button" wire:click="saveInvoice" wire:loading.attr="disabled" class="btn btn-primary btn-lg w-full">
          <span wire:loading.remove>Guardar y enviar</span>

          <span wire:loading class="flex items-center gap-2">
            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8z"></path>
            </svg>
            Procesando...
          </span>
        </button>

        @error('save') <span class="form-error mt-2 block">{{ $message }}</span> @enderror
      </div>
    </div>
  </div>
</div>
