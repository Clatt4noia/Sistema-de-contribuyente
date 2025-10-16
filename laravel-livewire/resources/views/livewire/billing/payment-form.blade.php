<div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h1 class="text-2xl font-semibold text-slate-900 ">{{ $isEdit ? 'Editar Pago' : 'Registrar Pago' }}</h1>
    <a href="{{ route('billing.payments.index') }}" class="btn btn-secondary">Volver</a>
 </div>

 <div class="surface-card p-6 shadow-lg">
 <form wire:submit="save" class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div class="form-field">
        <label for="payment_invoice_id" class="form-label">
            <span class="required">Factura</span>
        </label>
        <select
            id="payment_invoice_id"
            wire:model.defer="payment.invoice_id"
            class="form-control form-md @error('payment.invoice_id') is-invalid @enderror"
            @error('payment.invoice_id') aria-invalid="true" aria-describedby="payment_invoice_id-error" @enderror
        >
            <option value="">Seleccione una factura</option>
            @foreach($invoices as $invoice)
                <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }} - {{ optional($invoice->client)->business_name }}</option>
            @endforeach
        </select>
        @error('payment.invoice_id')
            <p id="payment_invoice_id-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="payment_amount" class="form-label">
            <span class="required">Monto</span>
        </label>
        <input
            id="payment_amount"
            type="number"
            step="0.01"
            wire:model.defer="payment.amount"
            class="form-control form-md @error('payment.amount') is-invalid @enderror"
            @error('payment.amount') aria-invalid="true" aria-describedby="payment_amount-error" @enderror
        >
        @error('payment.amount')
            <p id="payment_amount-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="payment_paid_at" class="form-label">
            <span class="required">Fecha</span>
        </label>
        <input
            id="payment_paid_at"
            type="date"
            wire:model.defer="payment.paid_at"
            class="form-control form-md @error('payment.paid_at') is-invalid @enderror"
            @error('payment.paid_at') aria-invalid="true" aria-describedby="payment_paid_at-error" @enderror
        >
        @error('payment.paid_at')
            <p id="payment_paid_at-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field">
        <label for="payment_method" class="form-label">Método</label>
        <input
            id="payment_method"
            type="text"
            wire:model.defer="payment.method"
            class="form-control form-md @error('payment.method') is-invalid @enderror"
            placeholder="Transferencia, efectivo..."
            @error('payment.method') aria-invalid="true" aria-describedby="payment_method-error" @enderror
        >
        @error('payment.method')
            <p id="payment_method-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field md:col-span-2">
        <label for="payment_reference" class="form-label">Referencia</label>
        <input
            id="payment_reference"
            type="text"
            wire:model.defer="payment.reference"
            class="form-control form-md @error('payment.reference') is-invalid @enderror"
            @error('payment.reference') aria-invalid="true" aria-describedby="payment_reference-error" @enderror
        >
        @error('payment.reference')
            <p id="payment_reference-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-field md:col-span-2">
        <label for="payment_notes" class="form-label">Notas</label>
        <textarea
            id="payment_notes"
            rows="4"
            wire:model.defer="payment.notes"
            class="form-control form-md @error('payment.notes') is-invalid @enderror"
            @error('payment.notes') aria-invalid="true" aria-describedby="payment_notes-error" @enderror
        ></textarea>
        @error('payment.notes')
            <p id="payment_notes-error" class="form-error">{{ $message }}</p>
        @enderror
    </div>
    <div class="md:col-span-2 flex justify-end">
        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Actualizar' : 'Guardar' }}</button>
    </div>
 </form>
 </div>
</div>
