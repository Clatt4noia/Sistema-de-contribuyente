<x-layouts.app :title="$isEdit ? __('Editar Chofer') : __('Registrar Chofer')">
    <div class="container mx-auto py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Editar Chofer' : 'Registrar Chofer' }}</h1>
            <a href="{{ route('fleet.drivers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Volver
            </a>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <input type="text" id="name" wire:model="driver.name" 
                            class="w-full px-3 py-2 border rounded-md">
                        @error('driver.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Apellido -->
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                        <input type="text" id="last_name" wire:model="driver.last_name" 
                            class="w-full px-3 py-2 border rounded-md">
                        @error('driver.last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Número de documento -->
                    <div>
                        <label for="document_number" class="block text-sm font-medium text-gray-700 mb-1">Número de documento</label>
                        <input type="text" id="document_number" wire:model="driver.document_number" 
                            class="w-full px-3 py-2 border rounded-md">
                        @error('driver.document_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Número de licencia -->
                    <div>
                        <label for="license_number" class="block text-sm font-medium text-gray-700 mb-1">Número de licencia</label>
                        <input type="text" id="license_number" wire:model="driver.license_number" 
                            class="w-full px-3 py-2 border rounded-md">
                        @error('driver.license_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Vencimiento de licencia -->
                    <div>
                        <label for="license_expiration" class="block text-sm font-medium text-gray-700 mb-1">Vencimiento de licencia</label>
                        <input type="date" id="license_expiration" wire:model="driver.license_expiration" 
                            class="w-full px-3 py-2 border rounded-md">
                        @error('driver.license_expiration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" id="phone" wire:model="driver.phone" 
                            class="w-full px-3 py-2 border rounded-md">
                        @error('driver.phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" wire:model="driver.email" 
                            class="w-full px-3 py-2 border rounded-md">
                        @error('driver.email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Estado -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select id="status" wire:model="driver.status" class="w-full px-3 py-2 border rounded-md">
                            <option value="active">Activo</option>
                            <option value="inactive">Inactivo</option>
                            <option value="on_leave">De permiso</option>
                        </select>
                        @error('driver.status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Dirección -->
                <div class="mt-6">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <input type="text" id="address" wire:model="driver.address" 
                        class="w-full px-3 py-2 border rounded-md">
                    @error('driver.address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Notas -->
                <div class="mt-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                    <textarea id="notes" wire:model="driver.notes" rows="4" 
                        class="w-full px-3 py-2 border rounded-md"></textarea>
                    @error('driver.notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mt-6">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        {{ $isEdit ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
