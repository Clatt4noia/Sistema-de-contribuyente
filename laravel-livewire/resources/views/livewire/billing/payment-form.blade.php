<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Editar Pago' : 'Registrar Pago' }}</h1>
        <a href="{{ route('billing.payments.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Volver</a>
    </div>

    <div class="bg-white shadow rounded p-6">
        <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Factura *</label>
                <select wire:model.defer="payment.invoice_id" class="w-full px-3 py-2 border rounded @error('payment.invoice_id') border-red-500 @enderror">
                    <option value="">Seleccione una factura</option>
                    @foreach($invoices as $invoice)
                        <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }} - {{ optional($invoice->client)->business_name }}</option>
                    @endforeach
                </select>
                @error('payment.invoice_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Monto *</label>
                <input type="number" step="0.01" wire:model.defer="payment.amount" class="w-full px-3 py-2 border rounded @error('payment.amount') border-red-500 @enderror">
                @error('payment.amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
                <input type="date" wire:model.defer="payment.paid_at" class="w-full px-3 py-2 border rounded @error('payment.paid_at') border-red-500 @enderror">
                @error('payment.paid_at') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Metodo</label>
                <input type="text" wire:model.defer="payment.method" class="w-full px-3 py-2 border rounded @error('payment.method') border-red-500 @enderror" placeholder="Transferencia, efectivo...">
                @error('payment.method') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Referencia</label>
                <input type="text" wire:model.defer="payment.reference" class="w-full px-3 py-2 border rounded @error('payment.reference') border-red-500 @enderror">
                @error('payment.reference') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                <textarea rows="4" wire:model.defer="payment.notes" class="w-full px-3 py-2 border rounded @error('payment.notes') border-red-500 @enderror"></textarea>
                @error('payment.notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">{{ $isEdit ? 'Actualizar' : 'Guardar' }}</button>
            </div>
        </form>
    </div>
</div>
