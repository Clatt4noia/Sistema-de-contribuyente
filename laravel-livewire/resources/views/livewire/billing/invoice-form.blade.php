<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Editar Factura' : 'Nueva Factura' }}</h1>
        <a href="{{ route('billing.invoices.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Volver</a>
    </div>

    <div class="bg-white shadow rounded p-6">
        <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Numero de factura *</label>
                <input type="text" wire:model.defer="invoice.invoice_number" class="w-full px-3 py-2 border rounded @error('invoice.invoice_number') border-red-500 @enderror">
                @error('invoice.invoice_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado *</label>
                <select wire:model.defer="invoice.status" class="w-full px-3 py-2 border rounded @error('invoice.status') border-red-500 @enderror">
                    <option value="draft">Borrador</option>
                    <option value="issued">Emitida</option>
                    <option value="paid">Pagada</option>
                    <option value="overdue">Vencida</option>
                </select>
                @error('invoice.status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                <select wire:model.defer="invoice.client_id" class="w-full px-3 py-2 border rounded @error('invoice.client_id') border-red-500 @enderror">
                    <option value="">Seleccione un cliente</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->business_name }}</option>
                    @endforeach
                </select>
                @error('invoice.client_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pedido asociado</label>
                <select wire:model.defer="invoice.order_id" class="w-full px-3 py-2 border rounded @error('invoice.order_id') border-red-500 @enderror">
                    <option value="">Sin pedido</option>
                    @foreach($orders as $order)
                        <option value="{{ $order->id }}">{{ $order->reference }}</option>
                    @endforeach
                </select>
                @error('invoice.order_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de emision *</label>
                <input type="date" wire:model.defer="invoice.issue_date" class="w-full px-3 py-2 border rounded @error('invoice.issue_date') border-red-500 @enderror">
                @error('invoice.issue_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de vencimiento</label>
                <input type="date" wire:model.defer="invoice.due_date" class="w-full px-3 py-2 border rounded @error('invoice.due_date') border-red-500 @enderror">
                @error('invoice.due_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subtotal *</label>
                <input type="number" step="0.01" wire:model.defer="invoice.subtotal" class="w-full px-3 py-2 border rounded @error('invoice.subtotal') border-red-500 @enderror">
                @error('invoice.subtotal') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Impuestos</label>
                <input type="number" step="0.01" wire:model.defer="invoice.tax" class="w-full px-3 py-2 border rounded @error('invoice.tax') border-red-500 @enderror">
                @error('invoice.tax') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                <input type="number" step="0.01" wire:model.defer="invoice.total" class="w-full px-3 py-2 border rounded @error('invoice.total') border-red-500 @enderror" placeholder="Se calcula si se deja vacio">
                @error('invoice.total') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                <textarea rows="4" wire:model.defer="invoice.notes" class="w-full px-3 py-2 border rounded @error('invoice.notes') border-red-500 @enderror"></textarea>
                @error('invoice.notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">{{ $isEdit ? 'Actualizar' : 'Guardar' }}</button>
            </div>
        </form>
    </div>
</div>
