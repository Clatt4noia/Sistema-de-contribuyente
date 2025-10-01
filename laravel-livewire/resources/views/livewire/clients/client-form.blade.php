<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Editar Cliente' : 'Registrar Cliente' }}</h1>
        <a href="{{ route('clients.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Volver</a>
    </div>

    <div class="bg-white shadow rounded p-6">
        <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Razon social *</label>
                <input type="text" wire:model.defer="form.business_name" class="w-full px-3 py-2 border rounded @error('form.business_name') border-red-500 @enderror">
                @error('form.business_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">RUC *</label>
                <input type="text" wire:model.defer="form.tax_id" class="w-full px-3 py-2 border rounded @error('form.tax_id') border-red-500 @enderror">
                @error('form.tax_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contacto</label>
                <input type="text" wire:model.defer="form.contact_name" class="w-full px-3 py-2 border rounded @error('form.contact_name') border-red-500 @enderror">
                @error('form.contact_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo</label>
                <input type="email" wire:model.defer="form.email" class="w-full px-3 py-2 border rounded @error('form.email') border-red-500 @enderror">
                @error('form.email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                <input type="text" wire:model.defer="form.phone" class="w-full px-3 py-2 border rounded @error('form.phone') border-red-500 @enderror">
                @error('form.phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Direccion de facturacion</label>
                <input type="text" wire:model.defer="form.billing_address" class="w-full px-3 py-2 border rounded @error('form.billing_address') border-red-500 @enderror">
                @error('form.billing_address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Condiciones de pago</label>
                <input type="text" wire:model.defer="form.payment_terms" class="w-full px-3 py-2 border rounded @error('form.payment_terms') border-red-500 @enderror" placeholder="30 dias, contado...">
                @error('form.payment_terms') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                <textarea rows="4" wire:model.defer="form.notes" class="w-full px-3 py-2 border rounded @error('form.notes') border-red-500 @enderror"></textarea>
                @error('form.notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">{{ $isEdit ? 'Actualizar' : 'Guardar' }}</button>
            </div>
        </form>
    </div>
</div>
