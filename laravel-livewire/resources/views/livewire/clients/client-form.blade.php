<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Editar Cliente' : 'Registrar Cliente' }}</h1>
        <a href="{{ route('clients.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Volver</a>
    </div>

    <div class="bg-white shadow rounded p-6">
        <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Razon social *</label>
                <input type="text" wire:model.defer="client.business_name" class="w-full px-3 py-2 border rounded @error('client.business_name') border-red-500 @enderror">
                @error('client.business_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">RUC *</label>
                <input type="text" wire:model.defer="client.tax_id" class="w-full px-3 py-2 border rounded @error('client.tax_id') border-red-500 @enderror">
                @error('client.tax_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contacto</label>
                <input type="text" wire:model.defer="client.contact_name" class="w-full px-3 py-2 border rounded @error('client.contact_name') border-red-500 @enderror">
                @error('client.contact_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo</label>
                <input type="email" wire:model.defer="client.email" class="w-full px-3 py-2 border rounded @error('client.email') border-red-500 @enderror">
                @error('client.email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                <input type="text" wire:model.defer="client.phone" class="w-full px-3 py-2 border rounded @error('client.phone') border-red-500 @enderror">
                @error('client.phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Direccion de facturacion</label>
                <input type="text" wire:model.defer="client.billing_address" class="w-full px-3 py-2 border rounded @error('client.billing_address') border-red-500 @enderror">
                @error('client.billing_address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Condiciones de pago</label>
                <input type="text" wire:model.defer="client.payment_terms" class="w-full px-3 py-2 border rounded @error('client.payment_terms') border-red-500 @enderror" placeholder="30 dias, contado...">
                @error('client.payment_terms') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                <textarea rows="4" wire:model.defer="client.notes" class="w-full px-3 py-2 border rounded @error('client.notes') border-red-500 @enderror"></textarea>
                @error('client.notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">{{ $isEdit ? 'Actualizar' : 'Guardar' }}</button>
            </div>
        </form>
    </div>
</div>
