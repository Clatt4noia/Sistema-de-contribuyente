<div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $isEdit ? 'Editar Pago' : 'Registrar Pago' }}</h1>
        <a href="{{ route('billing.payments.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-slate-700/70 dark:text-slate-200 dark:hover:bg-slate-900/60">Volver</a>
    </div>

    <div class="surface-card p-6 shadow-lg">
        <form wire:submit="save" class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="form-field">
                <label class="form-label">Factura *</label>
                <select wire:model.defer="payment.invoice_id" class="form-control @error('payment.invoice_id') border-rose-400 dark:border-rose-400 @enderror">
                    <option value="">Seleccione una factura</option>
                    @foreach($invoices as $invoice)
                        <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }} - {{ optional($invoice->client)->business_name }}</option>
                    @endforeach
                </select>
                @error('payment.invoice_id') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="form-field">
                <label class="form-label">Monto *</label>
                <input type="number" step="0.01" wire:model.defer="payment.amount" class="form-control @error('payment.amount') border-rose-400 dark:border-rose-400 @enderror">
                @error('payment.amount') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="form-field">
                <label class="form-label">Fecha *</label>
                <input type="date" wire:model.defer="payment.paid_at" class="form-control @error('payment.paid_at') border-rose-400 dark:border-rose-400 @enderror">
                @error('payment.paid_at') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="form-field">
                <label class="form-label">Metodo</label>
                <input type="text" wire:model.defer="payment.method" class="form-control @error('payment.method') border-rose-400 dark:border-rose-400 @enderror" placeholder="Transferencia, efectivo...">
                @error('payment.method') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="form-field md:col-span-2">
                <label class="form-label">Referencia</label>
                <input type="text" wire:model.defer="payment.reference" class="form-control @error('payment.reference') border-rose-400 dark:border-rose-400 @enderror">
                @error('payment.reference') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="form-field md:col-span-2">
                <label class="form-label">Notas</label>
                <textarea rows="4" wire:model.defer="payment.notes" class="form-control @error('payment.notes') border-rose-400 dark:border-rose-400 @enderror"></textarea>
                @error('payment.notes') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:bg-indigo-400 dark:text-slate-900 dark:hover:bg-indigo-300">{{ $isEdit ? 'Actualizar' : 'Guardar' }}</button>
            </div>
        </form>
    </div>
</div>
