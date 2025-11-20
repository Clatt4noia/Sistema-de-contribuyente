<div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h1 class="text-2xl font-semibold text-token ">{{ $isEdit ? 'Editar Factura' : 'Nueva Factura' }}</h1>
    <a href="{{ route('billing.invoices.index') }}" class="btn btn-secondary">Volver</a>
 </div>

 <div class="surface-card p-6 shadow-lg">
 <form wire:submit="save" class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div class="form-field">
        <label for="invoice_invoice_number" class="form-label">
            <span class="required">Número de comprobante</span>
        </label>
        <input
            id="invoice_invoice_number"
            type="text"
            wire:model.defer="invoice.invoice_number"
            class="form-control form-md @error('invoice.invoice_number') is-invalid @enderror"
            @error('invoice.invoice_number') aria-invalid="true" aria-describedby="invoice_invoice_number-error" @enderror
        >
        @error('invoice.invoice_number')
            <p id="invoice_invoice_number-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_status" class="form-label">
            <span class="required">Estado</span>
        </label>
        <select
            id="invoice_status"
            wire:model.defer="invoice.status"
            class="form-control form-md @error('invoice.status') is-invalid @enderror"
            @error('invoice.status') aria-invalid="true" aria-describedby="invoice_status-error" @enderror
        >
            <option value="draft">Borrador</option>
            <option value="issued">Emitida</option>
            <option value="paid">Pagada</option>
            <option value="overdue">Vencida</option>
        </select>
        @error('invoice.status')
            <p id="invoice_status-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_client_id" class="form-label">
            <span class="required">Cliente</span>
        </label>
        <select
            id="invoice_client_id"
            wire:model.defer="invoice.client_id"
            class="form-control form-md @error('invoice.client_id') is-invalid @enderror"
            @error('invoice.client_id') aria-invalid="true" aria-describedby="invoice_client_id-error" @enderror
        >
            <option value="">Seleccione un cliente</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->business_name }}</option>
            @endforeach
        </select>
        @error('invoice.client_id')
            <p id="invoice_client_id-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_order_id" class="form-label">Pedido asociado</label>
        <select
            id="invoice_order_id"
            wire:model.defer="invoice.order_id"
            class="form-control form-md @error('invoice.order_id') is-invalid @enderror"
            @error('invoice.order_id') aria-invalid="true" aria-describedby="invoice_order_id-error" @enderror
        >
            <option value="">Sin pedido</option>
            @foreach($orders as $order)
                <option value="{{ $order->id }}">{{ $order->reference }}</option>
            @endforeach
        </select>
        @error('invoice.order_id')
            <p id="invoice_order_id-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_transport_guide_id" class="form-label">Guía de remisión vinculada</label>
        <select
            id="invoice_transport_guide_id"
            wire:model.defer="invoice.transport_guide_id"
            class="form-control form-md @error('invoice.transport_guide_id') is-invalid @enderror"
            @error('invoice.transport_guide_id') aria-invalid="true" aria-describedby="invoice_transport_guide_id-error" @enderror
        >
            <option value="">Sin GRE</option>
            @foreach($transportGuides as $guide)
                <option value="{{ $guide->id }}">
                    {{ $guide->display_code }} — {{ $guide->client?->business_name }} (SUNAT: {{ strtoupper($guide->sunat_status ?? 'pendiente') }})
                </option>
            @endforeach
        </select>
        <p class="mt-2 text-xs text-token-muted">Muestra serie, correlativo y estado SUNAT de la GRE (GRE-T o GRE-R).</p>
        @error('invoice.transport_guide_id')
            <p id="invoice_transport_guide_id-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_document_type" class="form-label">
            <span class="required">Tipo de documento</span>
        </label>
        <select
            id="invoice_document_type"
            wire:model.defer="invoice.document_type"
            class="form-control form-md @error('invoice.document_type') is-invalid @enderror"
            @error('invoice.document_type') aria-invalid="true" aria-describedby="invoice_document_type-error" @enderror
        >
            <option value="01">Factura</option>
            <option value="03">Boleta</option>
            <option value="07">Nota de crédito</option>
            <option value="08">Nota de débito</option>
        </select>
        @error('invoice.document_type')
            <p id="invoice_document_type-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_series" class="form-label">
            <span class="required">Serie</span>
        </label>
        <input
            id="invoice_series"
            type="text"
            wire:model.defer="invoice.series"
            class="form-control form-md uppercase @error('invoice.series') is-invalid @enderror"
            maxlength="4"
            @error('invoice.series') aria-invalid="true" aria-describedby="invoice_series-error" @enderror
        >
        @error('invoice.series')
            <p id="invoice_series-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_correlative" class="form-label">
            <span class="required">Correlativo</span>
        </label>
        <input
            id="invoice_correlative"
            type="text"
            wire:model.defer="invoice.correlative"
            class="form-control form-md @error('invoice.correlative') is-invalid @enderror"
            maxlength="8"
            @error('invoice.correlative') aria-invalid="true" aria-describedby="invoice_correlative-error" @enderror
        >
        @error('invoice.correlative')
            <p id="invoice_correlative-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_ruc_emisor" class="form-label">
            <span class="required">RUC emisor</span>
        </label>
        <input
            id="invoice_ruc_emisor"
            type="text"
            wire:model.defer="invoice.ruc_emisor"
            class="form-control form-md @error('invoice.ruc_emisor') is-invalid @enderror"
            maxlength="11"
            @error('invoice.ruc_emisor') aria-invalid="true" aria-describedby="invoice_ruc_emisor-error" @enderror
        >
        @error('invoice.ruc_emisor')
            <p id="invoice_ruc_emisor-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_ruc_receptor" class="form-label">
            <span class="required">RUC cliente</span>
        </label>
        <input
            id="invoice_ruc_receptor"
            type="text"
            wire:model.defer="invoice.ruc_receptor"
            class="form-control form-md @error('invoice.ruc_receptor') is-invalid @enderror"
            maxlength="11"
            @error('invoice.ruc_receptor') aria-invalid="true" aria-describedby="invoice_ruc_receptor-error" @enderror
        >
        @error('invoice.ruc_receptor')
            <p id="invoice_ruc_receptor-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_currency" class="form-label">
            <span class="required">Moneda</span>
        </label>
        <select
            id="invoice_currency"
            wire:model.defer="invoice.currency"
            class="form-control form-md @error('invoice.currency') is-invalid @enderror"
            @error('invoice.currency') aria-invalid="true" aria-describedby="invoice_currency-error" @enderror
        >
            <option value="PEN">Soles (PEN)</option>
            <option value="USD">Dólares (USD)</option>
        </select>
        @error('invoice.currency')
            <p id="invoice_currency-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_issue_date" class="form-label">
            <span class="required">Fecha de emisión</span>
        </label>
        <input
            id="invoice_issue_date"
            type="date"
            wire:model.defer="invoice.issue_date"
            class="form-control form-md @error('invoice.issue_date') is-invalid @enderror"
            @error('invoice.issue_date') aria-invalid="true" aria-describedby="invoice_issue_date-error" @enderror
        >
        @error('invoice.issue_date')
            <p id="invoice_issue_date-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_due_date" class="form-label">Fecha de vencimiento</label>
        <input
            id="invoice_due_date"
            type="date"
            wire:model.defer="invoice.due_date"
            class="form-control form-md @error('invoice.due_date') is-invalid @enderror"
            @error('invoice.due_date') aria-invalid="true" aria-describedby="invoice_due_date-error" @enderror
        >
        @error('invoice.due_date')
            <p id="invoice_due_date-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_subtotal" class="form-label">
            <span class="required">Subtotal</span>
        </label>
        <input
            id="invoice_subtotal"
            type="number"
            step="0.01"
            wire:model.defer="invoice.subtotal"
            class="form-control form-md @error('invoice.subtotal') is-invalid @enderror"
            @error('invoice.subtotal') aria-invalid="true" aria-describedby="invoice_subtotal-error" @enderror
        >
        @error('invoice.subtotal')
            <p id="invoice_subtotal-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_taxable_amount" class="form-label">
            <span class="required">Base imponible</span>
        </label>
        <input
            id="invoice_taxable_amount"
            type="number"
            step="0.01"
            wire:model.defer="invoice.taxable_amount"
            class="form-control form-md @error('invoice.taxable_amount') is-invalid @enderror"
            @error('invoice.taxable_amount') aria-invalid="true" aria-describedby="invoice_taxable_amount-error" @enderror
        >
        @error('invoice.taxable_amount')
            <p id="invoice_taxable_amount-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_tax" class="form-label">Impuestos</label>
        <input
            id="invoice_tax"
            type="number"
            step="0.01"
            wire:model.defer="invoice.tax"
            class="form-control form-md @error('invoice.tax') is-invalid @enderror"
            @error('invoice.tax') aria-invalid="true" aria-describedby="invoice_tax-error" @enderror
        >
        @error('invoice.tax')
            <p id="invoice_tax-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_unaffected_amount" class="form-label">Monto inafecto</label>
        <input
            id="invoice_unaffected_amount"
            type="number"
            step="0.01"
            wire:model.defer="invoice.unaffected_amount"
            class="form-control form-md @error('invoice.unaffected_amount') is-invalid @enderror"
            @error('invoice.unaffected_amount') aria-invalid="true" aria-describedby="invoice_unaffected_amount-error" @enderror
        >
        @error('invoice.unaffected_amount')
            <p id="invoice_unaffected_amount-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_exempt_amount" class="form-label">Monto exonerado</label>
        <input
            id="invoice_exempt_amount"
            type="number"
            step="0.01"
            wire:model.defer="invoice.exempt_amount"
            class="form-control form-md @error('invoice.exempt_amount') is-invalid @enderror"
            @error('invoice.exempt_amount') aria-invalid="true" aria-describedby="invoice_exempt_amount-error" @enderror
        >
        @error('invoice.exempt_amount')
            <p id="invoice_exempt_amount-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="invoice_total" class="form-label">Total</label>
        <input
            id="invoice_total"
            type="number"
            step="0.01"
            wire:model.defer="invoice.total"
            class="form-control form-md @error('invoice.total') is-invalid @enderror"
            placeholder="Se calcula si se deja vacio"
            @error('invoice.total') aria-invalid="true" aria-describedby="invoice_total-error" @enderror
        >
        @error('invoice.total')
            <p id="invoice_total-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field md:col-span-2">
        <label for="invoice_notes" class="form-label">Notas</label>
        <textarea
            id="invoice_notes"
            rows="4"
            wire:model.defer="invoice.notes"
            class="form-control form-md @error('invoice.notes') is-invalid @enderror"
            @error('invoice.notes') aria-invalid="true" aria-describedby="invoice_notes-error" @enderror
        ></textarea>
        @error('invoice.notes')
            <p id="invoice_notes-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
 <div class="md:col-span-2 flex justify-end">
        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Actualizar' : 'Guardar' }}</button>
 </div>
 </form>
 </div>
</div>
