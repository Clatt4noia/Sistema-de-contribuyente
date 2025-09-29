<div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $isEdit ? 'Editar Factura' : 'Nueva Factura' }}</h1>
        <a href="{{ route('billing.invoices.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-slate-700/70 dark:text-slate-200 dark:hover:bg-slate-900/60">Volver</a>
    </div>

    <div class="surface-card p-6 shadow-lg">
        <form wire:submit="save" class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="form-field">
                <label class="form-label">Numero de factura *</label>
                <input type="text" wire:model.defer="invoice.invoice_number" class="form-control @error('invoice.invoice_number') border-rose-400 dark:border-rose-400 @enderror">
                @error('invoice.invoice_number') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="form-field">
                <label class="form-label">Estado *</label>
                <select wire:model.defer="invoice.status" class="form-control @error('invoice.status') border-rose-400 dark:border-rose-400 @enderror">
                    <option value="draft">Borrador</option>
                    <option value="issued">Emitida</option>
                    <option value="paid">Pagada</option>
                    <option value="overdue">Vencida</option>
                </select>
                @error('invoice.status') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="form-field">
                <label class="form-label">Cliente *</label>
                <select wire:model.defer="invoice.client_id" class="form-control @error('invoice.client_id') border-rose-400 dark:border-rose-400 @enderror">
                    <option value="">Seleccione un cliente</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->business_name }}</option>
                    @endforeach
                </select>
                @error('invoice.client_id') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="form-field">
                <label class="form-label">Pedido asociado</label>
                <select wire:model.defer="invoice.order_id" class="form-control @error('invoice.order_id') border-rose-400 dark:border-rose-400 @enderror">
                    <option value="">Sin pedido</option>
                    @foreach($orders as $order)
                        <option value="{{ $order->id }}">{{ $order->reference }}</option>
                    @endforeach
                </select>
                @error('invoice.order_id') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="form-field">
                <label class="form-label">Fecha de emision *</label>
                <input type="date" wire:model.defer="invoice.issue_date" class="form-control @error('invoice.issue_date') border-rose-400 dark:border-rose-400 @enderror">
                @error('invoice.issue_date') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="form-field">
                <label class="form-label">Fecha de vencimiento</label>
                <input type="date" wire:model.defer="invoice.due_date" class="form-control @error('invoice.due_date') border-rose-400 dark:border-rose-400 @enderror">
                @error('invoice.due_date') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="form-field">
                <label class="form-label">Subtotal *</label>
                <input type="number" step="0.01" wire:model.defer="invoice.subtotal" class="form-control @error('invoice.subtotal') border-rose-400 dark:border-rose-400 @enderror">
                @error('invoice.subtotal') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="form-field">
                <label class="form-label">Impuestos</label>
                <input type="number" step="0.01" wire:model.defer="invoice.tax" class="form-control @error('invoice.tax') border-rose-400 dark:border-rose-400 @enderror">
                @error('invoice.tax') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="form-field">
                <label class="form-label">Total</label>
                <input type="number" step="0.01" wire:model.defer="invoice.total" class="form-control @error('invoice.total') border-rose-400 dark:border-rose-400 @enderror" placeholder="Se calcula si se deja vacio">
                @error('invoice.total') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="form-field md:col-span-2">
                <label class="form-label">Notas</label>
                <textarea rows="4" wire:model.defer="invoice.notes" class="form-control @error('invoice.notes') border-rose-400 dark:border-rose-400 @enderror"></textarea>
                @error('invoice.notes') <span class="text-sm font-medium text-rose-500">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:bg-indigo-400 dark:text-slate-900 dark:hover:bg-indigo-300">{{ $isEdit ? 'Actualizar' : 'Guardar' }}</button>
            </div>
        </form>
    </div>
</div>
