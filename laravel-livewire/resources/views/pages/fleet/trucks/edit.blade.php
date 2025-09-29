<x-layouts.app.sidebar :title="__('Editar Camion')">
    <livewire:fleet.truck-form :truck="$truck" :key="'truck-form-edit-'.$truck->getKey()" />
</x-layouts.app.sidebar>
