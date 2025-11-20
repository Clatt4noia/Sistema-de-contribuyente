<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm text-token-muted">HU-14 · Seguimiento SUNAT</p>
            <h1 class="text-2xl font-semibold text-token">Tablero unificado de comprobantes y GRE</h1>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('billing.sunat-dashboard.export.excel') . '?' . $exportQuery }}" class="btn btn-secondary">Exportar Excel</a>
            <a href="{{ route('billing.sunat-dashboard.export.pdf') . '?' . $exportQuery }}" class="btn btn-ghost">Exportar PDF</a>
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

    <div class="surface-card space-y-4 p-6 shadow-lg">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div class="form-field">
                <label class="form-label" for="date_from">Desde</label>
                <input id="date_from" type="date" wire:model.live="dateFrom" class="form-control">
            </div>
            <div class="form-field">
                <label class="form-label" for="date_to">Hasta</label>
                <input id="date_to" type="date" wire:model.live="dateTo" class="form-control">
            </div>
            <div class="form-field">
                <label class="form-label" for="series">Serie</label>
                <input id="series" type="text" wire:model.live="series" class="form-control" placeholder="F001 / V001 / T001">
            </div>
            <div class="form-field">
                <label class="form-label" for="documentType">Tipo</label>
                <select id="documentType" wire:model.live="documentType" class="form-control">
                    <option value="all">Comprobantes y GRE</option>
                    <option value="invoice">Solo comprobantes</option>
                    <option value="gre">Solo GRE</option>
                </select>
            </div>
            <div class="form-field">
                <label class="form-label" for="sunatStatus">Estado SUNAT</label>
                <select id="sunatStatus" wire:model.live="sunatStatus" class="form-control">
                    <option value="">Todos</option>
                    <option value="aceptado">Aceptado</option>
                    <option value="rechazado">Rechazado</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="observado">Observado</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-md">
                <thead>
                    <tr class="table-row">
                        <th class="table-header">Documento</th>
                        <th class="table-header">Cliente</th>
                        <th class="table-header">Estado SUNAT</th>
                        <th class="table-header">Mensaje</th>
                        <th class="table-header">Emisión / Último envío</th>
                        <th class="table-header">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr class="table-row table-row-hover">
                            <td class="table-cell whitespace-nowrap text-sm">
                                <p class="font-semibold text-token">{{ $row['code'] }}</p>
                                <p class="text-xs text-token-muted">{{ $row['document_label'] }} · Serie {{ $row['series'] }}</p>
                            </td>
                            <td class="table-cell whitespace-nowrap text-sm">{{ $row['client'] ?? 'Sin cliente' }}</td>
                            <td class="table-cell whitespace-nowrap text-sm">
                                <livewire:billing.sunat-status-badge :status="$row['sunat_status']" :message="$row['sunat_message']" :key="'status-' . $row['type'] . '-' . $row['id']" />
                            </td>
                            <td class="table-cell text-sm text-token">{{ $row['sunat_message'] ?? 'Sin respuesta SUNAT' }}</td>
                            <td class="table-cell whitespace-nowrap text-sm text-token">
                                {{ optional($row['issued_at'])->format('d/m/Y') }}<br>
                                <span class="text-xs text-token-muted">Último envío: {{ optional($row['last_synced_at'])->format('d/m/Y H:i') ?? 'N/D' }}</span>
                            </td>
                            <td class="table-cell whitespace-nowrap text-sm">
                                @if($row['retryable'])
                                    <button wire:click="retry('{{ $row['type'] }}', {{ $row['id'] }})" class="btn btn-primary btn-sm">Reintentar</button>
                                @else
                                    <span class="text-token-muted">Sin acciones</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="6" class="table-empty">No se encontraron documentos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
