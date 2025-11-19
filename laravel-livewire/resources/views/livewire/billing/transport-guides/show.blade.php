<div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-token-muted">Guía de Remitente</p>
            <h1 class="text-2xl font-bold text-token">{{ $transportGuide->display_code }}</h1>
            <p class="text-sm text-token-muted">Emitida el {{ $transportGuide->issue_date?->format('d/m/Y') }} a las {{ $transportGuide->issue_time }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('billing.transport-guides.index') }}" class="btn btn-secondary">Volver</a>
            @can('update', $transportGuide)
                @if($transportGuide->sunat_status === \App\Models\TransportGuide::STATUS_DRAFT)
                    <a href="{{ route('billing.transport-guides.edit', $transportGuide) }}" class="btn btn-ghost">Editar</a>
                @endif
            @endcan
            @can('issue', $transportGuide)
                @if(in_array($transportGuide->sunat_status, [\App\Models\TransportGuide::STATUS_DRAFT, \App\Models\TransportGuide::STATUS_PENDING]))
                    <button class="btn btn-primary" wire:click="confirmIssue" wire:loading.attr="disabled">
                        <span wire:loading wire:target="confirmIssue,issueGuide" class="animate-spin">⏳</span>
                        Emitir SUNAT
                    </button>
                @endif
            @endcan
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="surface-card rounded-xl border border-token p-6 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <h2 class="text-lg font-semibold text-token">Remitente</h2>
                <p class="text-sm text-token">{{ $transportGuide->remitente_name }}</p>
                <p class="text-sm text-token-muted">RUC {{ $transportGuide->remitente_ruc }}</p>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-token">Transportista</h2>
                <p class="text-sm text-token">{{ $transportGuide->transportista_name }}</p>
                <p class="text-sm text-token-muted">RUC {{ $transportGuide->transportista_ruc }}</p>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-token">Punto de partida</h2>
                <p class="text-sm text-token">{{ $transportGuide->origin_address }}</p>
                <p class="text-sm text-token-muted">Ubigeo {{ $transportGuide->origin_ubigeo }}</p>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-token">Punto de llegada</h2>
                <p class="text-sm text-token">{{ $transportGuide->destination_address }}</p>
                <p class="text-sm text-token-muted">Ubigeo {{ $transportGuide->destination_ubigeo }}</p>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-token">Vehículo</h2>
                <p class="text-sm text-token">Placa {{ $transportGuide->vehicle_plate }} - {{ $transportGuide->vehicle_brand }}</p>
                <p class="text-sm text-token-muted">MTC {{ $transportGuide->mtc_registration_number }}</p>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-token">Conductor</h2>
                <p class="text-sm text-token">{{ $transportGuide->driver_name }}</p>
                <p class="text-sm text-token-muted">Licencia {{ $transportGuide->driver_license_number }} | Doc. {{ $transportGuide->driver_document_type }} {{ $transportGuide->driver_document_number }}</p>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-token">Traslado</h2>
                <p class="text-sm text-token">Motivo: {{ $transportGuide->transfer_reason_code }} {{ $transportGuide->transfer_reason_description }}</p>
                <p class="text-sm text-token-muted">Inicio: {{ $transportGuide->start_transport_date?->format('d/m/Y') }} @if($transportGuide->delivery_date) | Entrega: {{ $transportGuide->delivery_date->format('d/m/Y') }} @endif</p>
                <p class="text-sm text-token-muted">Peso bruto: {{ $transportGuide->gross_weight }} {{ $transportGuide->gross_weight_unit }}</p>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-token">SUNAT</h2>
                <p class="text-sm text-token">Estado interno: {{ $transportGuide->sunat_status }}</p>
                <p class="text-sm text-token-muted">Ticket: {{ $transportGuide->sunat_ticket ?: 'N/A' }}</p>
                <p class="text-sm text-token-muted">Notas: {{ $transportGuide->sunat_notes ?: 'Sin observaciones' }}</p>
            </div>
        </div>
    </div>

    <div class="surface-card rounded-xl border border-token p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-token mb-3">Bienes transportados</h2>
        <div class="overflow-x-auto">
            <table class="table table-md">
                <thead>
                    <tr class="table-row">
                        <th class="table-header">Descripción</th>
                        <th class="table-header">Unidad</th>
                        <th class="table-header">Cantidad</th>
                        <th class="table-header">Peso</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transportGuide->items as $item)
                        <tr class="table-row">
                            <td class="table-cell text-sm text-token">{{ $item->description }}</td>
                            <td class="table-cell text-sm text-token">{{ $item->unit_of_measure }}</td>
                            <td class="table-cell text-sm text-token">{{ $item->quantity }}</td>
                            <td class="table-cell text-sm text-token">{{ $item->weight ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="table-cell text-center text-sm text-token">Sin ítems registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @php
        $pdfUrl = $transportGuide->pdf_path ? \Illuminate\Support\Facades\URL::temporarySignedRoute('billing.transport-guides.download.pdf', now()->addMinutes(10), $transportGuide) : null;
        $xmlUrl = $transportGuide->xml_path ? \Illuminate\Support\Facades\URL::temporarySignedRoute('billing.transport-guides.download.xml', now()->addMinutes(10), $transportGuide) : null;
        $cdrUrl = $transportGuide->cdr_path ? \Illuminate\Support\Facades\URL::temporarySignedRoute('billing.transport-guides.download.cdr', now()->addMinutes(10), $transportGuide) : null;
    @endphp
    <div class="surface-card rounded-xl border border-token p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-token mb-3">Descargas</h2>
        <div class="flex flex-wrap gap-3">
            @if($pdfUrl)
                <a href="{{ $pdfUrl }}" class="btn btn-ghost">PDF</a>
            @endif
            @if($xmlUrl)
                <a href="{{ $xmlUrl }}" class="btn btn-ghost">XML</a>
            @endif
            @if($cdrUrl)
                <a href="{{ $cdrUrl }}" class="btn btn-ghost">CDR</a>
            @endif
        </div>
    </div>

    @if($confirmingIssue)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="surface-card w-full max-w-md rounded-xl p-6 shadow-lg">
                <h2 class="text-lg font-semibold text-token">Confirmar emisión</h2>
                <p class="mt-2 text-sm text-token">¿Deseas emitir esta guía a SUNAT?</p>
                <div class="mt-4 flex justify-end gap-3">
                    <button class="btn btn-secondary" wire:click="$set('confirmingIssue', false)">Cancelar</button>
                    <button class="btn btn-primary" wire:click="issueGuide" wire:loading.attr="disabled">
                        <span wire:loading wire:target="issueGuide" class="animate-spin">⏳</span>
                        Emitir ahora
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
