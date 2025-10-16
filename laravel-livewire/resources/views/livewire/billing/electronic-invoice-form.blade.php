<div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <div>
 <h1 class="text-2xl font-semibold text-token ">Emisión electrónica</h1>
 <p class="text-sm text-token ">SUNAT - {{ strtoupper(config('billing.sunat.mode')) }}</p>
 </div>
 <div class="flex items-center gap-3">
    <a href="{{ route('billing.invoices.index') }}" class="btn btn-secondary">Volver</a>
    <button type="button" wire:click="confirmSend" class="btn btn-primary">
        <x-heroicon-o-paper-airplane class="h-4 w-4" />
        Enviar a SUNAT
    </button>
 </div>
 </div>

 <div class="surface-card space-y-6 p-6 shadow-lg">
 <section class="grid grid-cols-1 gap-6 md:grid-cols-2">
 <div>
 <h2 class="text-lg font-semibold text-token ">Datos del comprobante</h2>
 <dl class="mt-3 space-y-2 text-sm text-token ">
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
 <h2 class="text-lg font-semibold text-token ">Totales</h2>
 <dl class="mt-3 space-y-2 text-sm text-token ">
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
 <dd class="font-bold text-success ">S/ {{ number_format($totals['total'], 2) }}</dd>
 </div>
 </dl>
 </div>
 </section>

 <section class="space-y-4">
 <h2 class="text-lg font-semibold text-token ">Detalle de ítems</h2>

 @error('items')
    <div class="rounded-xl border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-700 ">
 {{ $message }}
 </div>
 @enderror

        <div class="overflow-hidden rounded-xl border border-token ">
          <table class="table table-md">
            <thead>
              <tr class="table-row">
                <th class="table-header">Descripción</th>
                <th class="table-header">Cantidad</th>
                <th class="table-header">Precio unitario</th>
                <th class="table-header">IGV</th>
                <th class="table-header">Total</th>
                <th class="table-header text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @forelse($items as $index => $item)
                <tr class="table-row text-sm text-token ">
                  <td class="table-cell">
                    <p class="font-semibold">{{ $item['description'] }}</p>
                    <p class="text-xs text-token">{{ $item['unit_code'] }} • Excepción {{ $item['tax_exemption_reason'] }}</p>
                  </td>
                  <td class="table-cell">{{ number_format($item['quantity'], 2) }}</td>
                  <td class="table-cell">S/ {{ number_format($item['unit_price'], 2) }}</td>
                  <td class="table-cell">S/ {{ number_format($item['tax_amount'], 2) }}</td>
                  <td class="table-cell font-semibold">S/ {{ number_format($item['taxable_amount'] + $item['tax_amount'], 2) }}</td>
                  <td class="table-cell text-right">
                    <button type="button" wire:click="removeItem({{ $index }})" class="btn btn-danger btn-sm">
                      Quitar
                    </button>
                  </td>
                </tr>
              @empty
                <tr class="table-row">
                  <td colspan="6" class="table-empty">Sin ítems registrados.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
 </section>

 <section class="space-y-4">
 <h2 class="text-lg font-semibold text-token ">Agregar ítem</h2>
 <div class="grid grid-cols-1 gap-4 md:grid-cols-6">
        <div class="md:col-span-2 form-field">
            <label for="item_description" class="form-label">
                <span class="required">Descripción</span>
            </label>
            <input
                id="item_description"
                type="text"
                wire:model.defer="newItem.description"
                class="form-control form-md @error('newItem.description') is-invalid @enderror"
                @error('newItem.description') aria-invalid="true" aria-describedby="item_description-error" @enderror
            >
            @error('newItem.description')
                <p id="item_description-error" class="form-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-field">
            <label for="item_quantity" class="form-label">
                <span class="required">Cantidad</span>
            </label>
            <input
                id="item_quantity"
                type="number"
                step="0.01"
                wire:model.defer="newItem.quantity"
                class="form-control form-md @error('newItem.quantity') is-invalid @enderror"
                @error('newItem.quantity') aria-invalid="true" aria-describedby="item_quantity-error" @enderror
            >
            @error('newItem.quantity')
                <p id="item_quantity-error" class="form-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-field">
            <label for="item_unit_price" class="form-label">
                <span class="required">Precio unitario</span>
            </label>
            <input
                id="item_unit_price"
                type="number"
                step="0.01"
                wire:model.defer="newItem.unit_price"
                class="form-control form-md @error('newItem.unit_price') is-invalid @enderror"
                @error('newItem.unit_price') aria-invalid="true" aria-describedby="item_unit_price-error" @enderror
            >
            @error('newItem.unit_price')
                <p id="item_unit_price-error" class="form-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-field">
            <label for="item_tax_percentage" class="form-label">IGV (%)</label>
            <input
                id="item_tax_percentage"
                type="number"
                step="0.01"
                wire:model.defer="newItem.tax_percentage"
                class="form-control form-md @error('newItem.tax_percentage') is-invalid @enderror"
                @error('newItem.tax_percentage') aria-invalid="true" aria-describedby="item_tax_percentage-error" @enderror
            >
            @error('newItem.tax_percentage')
                <p id="item_tax_percentage-error" class="form-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-field">
            <label for="item_unit_code" class="form-label">Código unidad</label>
            <input
                id="item_unit_code"
                type="text"
                wire:model.defer="newItem.unit_code"
                maxlength="3"
                class="form-control form-md @error('newItem.unit_code') is-invalid @enderror"
                @error('newItem.unit_code') aria-invalid="true" aria-describedby="item_unit_code-error" @enderror
            >
            @error('newItem.unit_code')
                <p id="item_unit_code-error" class="form-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-field">
            <label for="item_price_type_code" class="form-label">Tipo de precio</label>
            <input
                id="item_price_type_code"
                type="text"
                wire:model.defer="newItem.price_type_code"
                maxlength="2"
                class="form-control form-md @error('newItem.price_type_code') is-invalid @enderror"
                @error('newItem.price_type_code') aria-invalid="true" aria-describedby="item_price_type_code-error" @enderror
            >
            @error('newItem.price_type_code')
                <p id="item_price_type_code-error" class="form-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-field">
            <label for="item_tax_exemption_reason" class="form-label">Motivo de exoneración</label>
            <input
                id="item_tax_exemption_reason"
                type="text"
                wire:model.defer="newItem.tax_exemption_reason"
                maxlength="2"
                class="form-control form-md @error('newItem.tax_exemption_reason') is-invalid @enderror"
                @error('newItem.tax_exemption_reason') aria-invalid="true" aria-describedby="item_tax_exemption_reason-error" @enderror
            >
            @error('newItem.tax_exemption_reason')
                <p id="item_tax_exemption_reason-error" class="form-error">{{ $message }}</p>
            @enderror
        </div>
 <div class="md:col-span-2 flex items-end">
    <button type="button" wire:click="addItem" class="btn btn-primary w-full">
        <x-heroicon-o-plus-small class="h-4 w-4" /> Agregar
    </button>
 </div>
 </div>
 </section>
 </div>

 @if($confirmationOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-surface-strong px-4 py-8">
        <div class="w-full max-w-lg rounded-2xl bg-elevated p-6 shadow-2xl ">
 <h2 class="text-lg font-semibold text-token ">Confirmar envío</h2>
 <p class="mt-3 text-sm text-token ">¿Confirmas que deseas enviar el comprobante {{ $invoice->numero_completo }} a SUNAT? Asegúrate de que la información sea correcta antes de proceder.</p>
 <div class="mt-6 flex justify-end gap-3">
    <button type="button" wire:click="$set('confirmationOpen', false)" class="btn btn-secondary">Cancelar</button>
    <button type="button" wire:click="sendToSunat" class="btn btn-primary">Enviar ahora</button>
 </div>
 </div>
 </div>
 @endif
</div>
