<div class="space-y-6">
    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Emitir comprobante SUNAT</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Complete los datos para generar la factura electrónica.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" wire:click="$dispatch('open-client-modal')"
                class="inline-flex items-center gap-2 rounded-xl border border-indigo-200/60 px-4 py-2 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:border-indigo-500/40 dark:text-indigo-300 dark:hover:bg-indigo-500/10">
                <x-heroicon-o-user-plus class="h-4 w-4" />
                Nuevo cliente
            </button>
            <a href="{{ route('billing.invoices.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-400 dark:border-slate-700/70 dark:text-slate-200 dark:hover:bg-slate-900/60">
                Volver
            </a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[2fr,1fr]">
        <div class="space-y-6">
            <div class="surface-card space-y-6 p-6 shadow-lg">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Datos del comprobante</h2>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div class="form-field">
                        <label class="form-label">Tipo de comprobante</label>
                        <select wire:model="documentType" class="form-control">
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
                        <input type="text" wire:model="series" maxlength="4" class="form-control uppercase" />
                        @error('series') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-field">
                        <label class="form-label">Correlativo</label>
                        <input type="text" value="{{ $correlative }}" readonly class="form-control cursor-not-allowed bg-slate-100 text-slate-500 dark:bg-slate-800/60 dark:text-slate-400" />
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

            <div class="surface-card space-y-6 p-6 shadow-lg">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Cliente</h2>
                <div class="space-y-3">
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="clientSearch" placeholder="Buscar por RUC o razón social"
                               class="form-control" />
                        @if(!empty($clientResults))
                            <ul class="absolute z-20 mt-1 w-full rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-800">
                                @foreach($clientResults as $client)
                                    <li>
                                        <button type="button" wire:click="selectClient({{ $client['id'] }})"
                                                class="flex w-full items-start gap-2 px-4 py-2 text-left text-sm hover:bg-indigo-50 focus:bg-indigo-50 dark:hover:bg-indigo-500/20">
                                            <div class="font-medium text-slate-800 dark:text-slate-100">{{ $client['name'] }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $client['document'] }}</div>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    @error('clientSearch') <span class="form-error">{{ $message }}</span> @enderror

                    @if($selectedClient)
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50 p-4 text-sm text-slate-700 dark:border-slate-700/70 dark:bg-slate-900/60 dark:text-slate-300">
                            <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $selectedClient['name'] }}</div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $selectedClient['document'] }}</div>
                            @if($selectedClient['billing_address'])
                                <p class="mt-2 text-xs">{{ $selectedClient['billing_address'] }}</p>
                            @endif
                            <div class="mt-2 flex flex-wrap gap-4 text-xs">
                                @if($selectedClient['email'])
                                    <span class="flex items-center gap-1"><x-heroicon-o-envelope class="h-4 w-4" /> {{ $selectedClient['email'] }}</span>
                                @endif
                                @if($selectedClient['phone'])
                                    <span class="flex items-center gap-1"><x-heroicon-o-phone class="h-4 w-4" /> {{ $selectedClient['phone'] }}</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="surface-card space-y-6 p-6 shadow-lg">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Pedidos a facturar</h2>
                    <div class="grid w-full gap-4 md:w-auto md:grid-cols-[minmax(0,1fr)_220px] md:items-end">
                        <div class="form-field md:mb-0">
                            <label class="form-label">Buscar pedido</label>
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="orderSearch" placeholder="Buscar pedido por referencia, origen o destino"
                                       class="form-control {{ $selectedClient ? '' : 'cursor-not-allowed opacity-60' }}"
                                       @disabled(!$selectedClient) />
                                @if($selectedClient && !empty($orderResults))
                                    <ul class="absolute z-20 mt-1 w-full rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-800">
                                        @foreach($orderResults as $order)
                                            <li>
                                                <button type="button" wire:click="addOrder({{ $order['id'] }})"
                                                        class="flex w-full flex-col gap-1 px-4 py-2 text-left text-sm hover:bg-indigo-50 focus:bg-indigo-50 dark:hover:bg-indigo-500/20">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="font-medium text-slate-800 dark:text-slate-100">Pedido {{ $order['reference'] }}</span>
                                                        @if($order['cargo_type'])
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-slate-600 dark:bg-slate-800/60 dark:text-slate-300">
                                                                {{ $order['cargo_type'] }}
                                                                @if($order['is_hazardous'])
                                                                    <x-heroicon-o-exclamation-triangle class="h-3 w-3 text-amber-500" />
                                                                @endif
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if($order['destination'] || $order['origin'])
                                                        <span class="text-xs text-slate-500 dark:text-slate-400">
                                                            @if($order['origin'])
                                                                Origen: {{ $order['origin'] }}
                                                            @endif
                                                            @if($order['destination'])
                                                                <span class="ml-1">Destino: {{ $order['destination'] }}</span>
                                                            @endif
                                                        </span>
                                                    @endif
                                                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ $this->currencySymbol }} {{ number_format($order['estimated_cost'], 2) }}</span>
                                                    @if($order['pickup_date'] || $order['status_label'])
                                                        <span class="text-xs text-slate-400 dark:text-slate-500">
                                                            @if($order['pickup_date'])
                                                                Recojo: {{ $order['pickup_date'] }}
                                                            @endif
                                                            @if($order['status_label'])
                                                                <span class="ml-1 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600 dark:bg-slate-800/60 dark:text-slate-300">{{ $order['status_label'] }}</span>
                                                            @endif
                                                        </span>
                                                    @endif
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                        <div class="form-field md:mb-0">
                            <label class="form-label">Tipo de carga</label>
                            <select wire:model="cargoTypeFilter" class="form-control {{ $selectedClient ? '' : 'cursor-not-allowed opacity-60' }}" @disabled(!$selectedClient)>
                                <option value="">Todos los tipos</option>
                                @foreach($cargoTypes as $type)
                                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                @if(!$selectedClient)
                    <p class="text-xs text-slate-500 dark:text-slate-400">Seleccione un cliente para listar sus pedidos pendientes de facturar.</p>
                @elseif(empty($orderResults) && ($orderSearch !== '' || $cargoTypeFilter))
                    <p class="text-xs text-slate-500 dark:text-slate-400">No se encontraron pedidos pendientes que coincidan con los filtros aplicados.</p>
                @endif

                <div class="overflow-hidden rounded-xl border border-slate-200/70 shadow-sm dark:border-slate-700/70">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-3 text-left">Cantidad</th>
                                <th class="px-4 py-3 text-left">Descripción</th>
                                <th class="px-4 py-3 text-right">Precio unit.</th>
                                <th class="px-4 py-3 text-right">Base imponible</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70 bg-white text-sm dark:divide-slate-700/70 dark:bg-slate-900/40 dark:text-slate-100">
                            @forelse($invoiceItems as $index => $item)
                                <tr wire:key="item-{{ $item['order_id'] ?? $index }}">
                                    <td class="px-4 py-3">
                                        <input type="number" min="0" step="0.01" wire:model.lazy="invoiceItems.{{ $index }}.quantity"
                                               wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                               class="form-control text-right" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $item['description'] }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $item['reference'] ?? $item['sku'] ?? '' }}</div>
                                        @if(!empty($item['cargo_type']))
                                            <div class="mt-1 inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-slate-600 dark:bg-slate-800/60 dark:text-slate-300">
                                                {{ $item['cargo_type'] }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ $this->currencySymbol }} {{ number_format($item['unit_price'], 2) }}</td>
                                    <td class="px-4 py-3 text-right">{{ $this->currencySymbol }} {{ number_format($item['taxable_amount'] ?? 0, 2) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                                class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:hover:bg-rose-500/20">
                                            <x-heroicon-o-trash class="h-4 w-4" />
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                                        No se han agregado pedidos. Utilice el buscador para añadir ítems.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @error('invoiceItems') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="space-y-6">
            <div class="surface-card space-y-4 p-6 shadow-lg">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Totales</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">Sub total</dt>
                        <dd class="font-semibold text-slate-900 dark:text-slate-100">{{ $this->currencySymbol }} {{ number_format($subtotal, 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">IGV ({{ rtrim(rtrim(number_format($this->taxRate, 2), '0'), '.') }}%)</dt>
                        <dd class="font-semibold text-slate-900 dark:text-slate-100">{{ $this->currencySymbol }} {{ number_format($igv, 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between text-base">
                        <dt class="font-semibold text-slate-600 dark:text-slate-200">Importe total</dt>
                        <dd class="font-bold text-indigo-600 dark:text-indigo-300">{{ $this->currencySymbol }} {{ number_format($total, 2) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="surface-card p-6 shadow-lg">
                <button type="button" wire:click="saveInvoice" wire:loading.attr="disabled"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-500 px-5 py-3 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 disabled:opacity-60 dark:bg-indigo-400 dark:text-slate-900 dark:hover:bg-indigo-300">
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
