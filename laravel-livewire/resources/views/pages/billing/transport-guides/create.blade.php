<x-layouts.app.sidebar :title="__('Registrar Guía de Remisión')">
    <livewire:billing.transport-guides.transport-guide-form :type="$type ?? \App\Models\TransportGuide::TYPE_TRANSPORTISTA" :key="'transport-guide-form-create-' . ($type ?? 'transportista')" />
</x-layouts.app.sidebar>
