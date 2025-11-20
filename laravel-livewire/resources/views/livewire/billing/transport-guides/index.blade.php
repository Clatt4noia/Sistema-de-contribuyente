<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    @php
        $isTransportista = $type === \App\Models\TransportGuide::TYPE_TRANSPORTISTA;
        $createRoute = $isTransportista ? route('billing.transport-guides.create') : route('billing.remitter-guides.create');
    @endphp
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold text-token">
            {{ $isTransportista ? 'Guías de transportista (GRE-T)' : 'Guías de remitente (GRE-R)' }}
        </h1>
        @can('create', App\Models\TransportGuide::class)
            <a href="{{ $createRoute }}" class="btn btn-primary">Nueva guía</a>
        @endcan
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

    <div class="surface-card overflow-hidden shadow-lg">
        <div class="grid grid-cols-1 gap-4 border-b border-token px-4 py-4 md:grid-cols-5">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Buscar por serie o código" class="form-control">
            <select wire:model.live="status" class="form-control">
                <option value="">Todos los estados</option>
                @foreach($statusLabels as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="dateFrom" class="form-control" placeholder="Desde">
            <input type="date" wire:model.live="dateTo" class="form-control" placeholder="Hasta">
            <div class="flex items-center justify-end gap-3">
                <span class="text-xs text-token-muted">{{ $guides->total() }} registros</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-md">
                <thead>
                    <tr class="table-row">
                        <th class="table-header">Código</th>
                        <th class="table-header">Emisión</th>
                        <th class="table-header">Serie / Número</th>
                        <th class="table-header">Vehículo</th>
                        <th class="table-header">Conductor</th>
                        <th class="table-header">Estado interno</th>
                        <th class="table-header">Estado SUNAT</th>
                        <th class="table-header">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guides as $guide)
                        @php
                            $sunatLabel = $statusLabels[$guide->sunat_status] ?? ucfirst($guide->sunat_status);
                            $statusClasses = [
                                'draft' => 'bg-surface-strong text-token',
                                'pending' => 'bg-warning-soft text-warning-strong',
                                'sent' => 'bg-primary-soft text-primary-strong',
                                'accepted' => 'bg-success-soft text-success-strong',
                                'rejected' => 'bg-danger-soft text-danger-strong',
                                'cancelled' => 'bg-surface-strong text-token',
                                'error' => 'bg-danger-soft text-danger-strong',
                            ];
                        @endphp
                        <tr class="table-row table-row-hover">
                            <td class="table-cell whitespace-nowrap text-sm font-semibold text-token">{{ $guide->display_code }}</td>
                            <td class="table-cell whitespace-nowrap text-sm text-token">{{ $guide->issue_date?->format('d/m/Y') }}</td>
                            <td class="table-cell whitespace-nowrap text-sm text-token">{{ $guide->series }}-{{ str_pad($guide->correlative, 8, '0', STR_PAD_LEFT) }}</td>
                            <td class="table-cell whitespace-nowrap text-sm text-token">{{ $guide->vehicle_plate }}</td>
                            <td class="table-cell whitespace-nowrap text-sm text-token">{{ $guide->driver_name }}</td>
                            <td class="table-cell whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses[$guide->sunat_status] ?? 'bg-surface-strong text-token' }}">
                                    {{ $sunatLabel }}
                                </span>
                            </td>
                            <td class="table-cell whitespace-nowrap text-sm">
                                <livewire:billing.sunat-status-badge :status="$guide->sunat_status" :message="$guide->sunat_notes" :key="'status-'.$guide->id" />
                            </td>
                            <td class="table-cell whitespace-nowrap text-sm font-medium space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('billing.transport-guides.show', $guide) }}" class="btn btn-ghost btn-sm">Ver</a>
                                    @can('update', $guide)
                                        @if($guide->sunat_status === \App\Models\TransportGuide::STATUS_DRAFT)
                                            <a href="{{ route('billing.transport-guides.edit', $guide) }}" class="btn btn-secondary btn-sm">Editar</a>
                                        @endif
                                    @endcan
                                    @can('issue', $guide)
                                        @if(in_array($guide->sunat_status, [\App\Models\TransportGuide::STATUS_DRAFT, \App\Models\TransportGuide::STATUS_PENDING]))
                                            <button wire:click="confirmIssue({{ $guide->id }})" class="btn btn-primary btn-sm" wire:loading.attr="disabled">Emitir SUNAT</button>
                                        @endif
                                    @endcan
                                </div>
                                @php
                                    $signedPdf = $guide->pdf_path ? route('billing.transport-guides.pdf', $guide) : null;
                                    $signedXml = $guide->xml_path ? route('billing.transport-guides.xml', $guide) : null;
                                    $signedCdr = $guide->cdr_path ? route('billing.transport-guides.cdr', $guide) : null;

                                @endphp
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($signedPdf)
                                        <a href="{{ $signedPdf }}" class="btn btn-ghost btn-sm">PDF</a>
                                    @endif
                                    @if($signedXml)
                                        <a href="{{ $signedXml }}" class="btn btn-ghost btn-sm">XML</a>
                                    @endif
                                    @if($signedCdr)
                                        <a href="{{ $signedCdr }}" class="btn btn-ghost btn-sm">CDR</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="table-cell text-center text-sm text-token">No se encontraron guías.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-token px-4 py-3">{{ $guides->links() }}</div>
    </div>

    @if($confirmingIssue)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="surface-card w-full max-w-md rounded-xl p-6 shadow-lg">
                <h2 class="text-lg font-semibold text-token">Confirmar emisión</h2>
                <p class="mt-2 text-sm text-token">¿Deseas emitir esta guía a SUNAT? Se validará la disponibilidad y se generará el XML UBL.</p>
                <div class="mt-4 flex justify-end gap-3">
                    <button class="btn btn-secondary" wire:click="$set('confirmingIssue', false)">Cancelar</button>
                    <button class="btn btn-primary" wire:click="issueSelectedGuide" wire:loading.attr="disabled">
                        <span wire:loading wire:target="issueSelectedGuide" class="animate-spin">⏳</span>
                        Emitir ahora
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
