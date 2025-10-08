<div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Emisión electrónica</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400">SUNAT - {{ strtoupper(config('billing.sunat.mode')) }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('billing.invoices.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-slate-700/70 dark:text-slate-200 dark:hover:bg-slate-900/60">Volver</a>
            <button type="button" wire:click="confirmSend" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-emerald-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500 dark:bg-emerald-400 dark:text-slate-900 dark:hover:bg-emerald-300">
                <x-heroicon-o-paper-airplane class="h-4 w-4" />
                Enviar a SUNAT
            </button>
        </div>
    </div>

    <div class="surface-card space-y-6 p-6 shadow-lg">
        <section class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Datos del comprobante</h2>
                <dl class="mt-3 space-y-2 text-sm text-slate-700 dark:text-slate-300">
                    <div class="flex items-center justify-between">
                        <dt>Serie</dt>
                        <dd class="font-medium">{{ $invoice->series }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt>Correlativo</dt>
                        <dd class="font-medium">{{ $invoice->correlative }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt>Cliente</dt>
                        <dd class="font-medium">{{ $invoice->client->business_name ?? 'Cliente' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt>RUC cliente</dt>
                        <dd class="font-medium">{{ $invoice->ruc_receptor }}</dd>
                    </div>
                </dl>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Totales</h2>
                <dl class="mt-3 space-y-2 text-sm text-slate-700 dark:text-slate-300">
                    <div class="flex items-center justify-between">
                        <dt>Base imponible</dt>
                        <dd class="font-semibold">S/ {{ number_format($totals['subtotal'], 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt>IGV (18%)</dt>
                        <dd class="font-semibold">S/ {{ number_format($totals['tax'], 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between text-lg">
                        <dt>Total</dt>
                        <dd class="font-bold text-emerald-600 dark:text-emerald-400">S/ {{ number_format($totals['total'], 2) }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Detalle de ítems</h2>

            @error('items')
                <div class="rounded-xl border border-rose-400/70 bg-rose-50 px-4 py-3 text-sm text-rose-600 dark:border-rose-600/60 dark:bg-rose-950/40 dark:text-rose-200">
                    {{ $message }}
                </div>
            @enderror

            <div class="overflow-hidden rounded-xl border border-slate-200/60 dark:border-slate-700/80">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-900/40">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">
                            <th class="px-4 py-3">Descripción</th>
                            <th class="px-4 py-3">Cantidad</th>
                            <th class="px-4 py-3">Precio unitario</th>
                            <th class="px-4 py-3">IGV</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($items as $index => $item)
                            <tr class="text-sm text-slate-700 dark:text-slate-200">
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ $item['description'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $item['unit_code'] }} • Excepción {{ $item['tax_exemption_reason'] }}</p>
                                </td>
                                <td class="px-4 py-3">{{ number_format($item['quantity'], 2) }}</td>
                                <td class="px-4 py-3">S/ {{ number_format($item['unit_price'], 2) }}</td>
                                <td class="px-4 py-3">S/ {{ number_format($item['tax_amount'], 2) }}</td>
                                <td class="px-4 py-3 font-semibold">S/ {{ number_format($item['taxable_amount'] + $item['tax_amount'], 2) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" wire:click="removeItem({{ $index }})" class="inline-flex items-center gap-2 rounded-lg border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-400/60 dark:text-rose-200 dark:hover:bg-rose-900/40">
                                        Quitar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">Sin ítems registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Agregar ítem</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-6">
                <div class="md:col-span-2">
                    <label class="form-label">Descripción *</label>
                    <input type="text" wire:model.defer="newItem.description" class="form-control @error('newItem.description') border-rose-400 dark:border-rose-400 @enderror">
                    @error('newItem.description') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Cantidad *</label>
                    <input type="number" step="0.01" wire:model.defer="newItem.quantity" class="form-control @error('newItem.quantity') border-rose-400 dark:border-rose-400 @enderror">
                    @error('newItem.quantity') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Precio unitario *</label>
                    <input type="number" step="0.01" wire:model.defer="newItem.unit_price" class="form-control @error('newItem.unit_price') border-rose-400 dark:border-rose-400 @enderror">
                    @error('newItem.unit_price') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">IGV (%)</label>
                    <input type="number" step="0.01" wire:model.defer="newItem.tax_percentage" class="form-control @error('newItem.tax_percentage') border-rose-400 dark:border-rose-400 @enderror">
                    @error('newItem.tax_percentage') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Código unidad</label>
                    <input type="text" wire:model.defer="newItem.unit_code" maxlength="3" class="form-control @error('newItem.unit_code') border-rose-400 dark:border-rose-400 @enderror">
                    @error('newItem.unit_code') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Tipo de precio</label>
                    <input type="text" wire:model.defer="newItem.price_type_code" maxlength="2" class="form-control @error('newItem.price_type_code') border-rose-400 dark:border-rose-400 @enderror">
                    @error('newItem.price_type_code') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Motivo de exoneración</label>
                    <input type="text" wire:model.defer="newItem.tax_exemption_reason" maxlength="2" class="form-control @error('newItem.tax_exemption_reason') border-rose-400 dark:border-rose-400 @enderror">
                    @error('newItem.tax_exemption_reason') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2 flex items-end">
                    <button type="button" wire:click="addItem" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-200">
                        <x-heroicon-o-plus-small class="h-4 w-4" /> Agregar
                    </button>
                </div>
            </div>
        </section>
    </div>

    @if($confirmationOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 py-8">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Confirmar envío</h2>
                <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">¿Confirmas que deseas enviar el comprobante {{ $invoice->numero_completo }} a SUNAT? Asegúrate de que la información sea correcta antes de proceder.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="$set('confirmationOpen', false)" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancelar</button>
                    <button type="button" wire:click="sendToSunat" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-emerald-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500 dark:bg-emerald-400 dark:text-emerald-950 dark:hover:bg-emerald-300">Enviar ahora</button>
                </div>
            </div>
        </div>
    @endif
</div>
