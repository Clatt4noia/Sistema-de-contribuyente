<x-layouts.app.sidebar :title="__('Editar Chofer')">
    <livewire:fleet.driver-form :driver="$driver" :key="'driver-form-edit-' . $driver->getKey()" />
</x-layouts.app.sidebar>
