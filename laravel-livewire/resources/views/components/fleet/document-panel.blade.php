@props([
    'isEdit' => false,
    'documentableType' => null,
    'documentableId' => null,
    'pendingMessage' => 'Guarda el registro para habilitar la gestión de documentos.',
])

@if ($isEdit && $documentableId)
    <livewire:fleet.document-manager
        :documentable-type="$documentableType"
        :documentable-id="$documentableId"
        :key="$documentableType . '-documents-' . $documentableId"
    />
@else
    <div class="rounded-2xl border border-dashed border-token bg-surface p-6 text-sm text-token ">
        {{ $pendingMessage }}
    </div>
@endif
