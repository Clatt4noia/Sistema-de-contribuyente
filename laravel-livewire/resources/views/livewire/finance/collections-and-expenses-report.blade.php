<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm text-token-muted">HU-15 · Finanzas</p>
            <h1 class="text-2xl font-semibold text-token">Reporte de cobranzas y gastos operativos</h1>
        </div>
        <button wire:click="exportExcel" class="btn btn-secondary">Exportar Excel/CSV</button>
    </div>

    <div class="surface-card p-6 shadow-lg space-y-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div class="form-field">
                <label class="form-label" for="startDate">Desde</label>
                <input id="startDate" type="date" wire:model.live="startDate" class="form-control">
            </div>
            <div class="form-field">
                <label class="form-label" for="endDate">Hasta</label>
                <input id="endDate" type="date" wire:model.live="endDate" class="form-control">
            </div>
            <div class="form-field">
                <label class="form-label" for="period">Periodo</label>
                <select id="period" wire:model.live="period" class="form-control">
                    <option value="month">Mensual</option>
                    <option value="week">Semanal</option>
                    <option value="day">Diario</option>
                </select>
            </div>
            <div class="form-field">
                <label class="form-label" for="client">Cliente</label>
                <select id="client" wire:model.live="clientId" class="form-control">
                    <option value="">Todos</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->business_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-field">
                <label class="form-label" for="vehicle">Vehículo</label>
                <select id="vehicle" wire:model.live="vehicleId" class="form-control">
                    <option value="">Todos</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->plate_number }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="form-field">
                <label class="form-label" for="routeFilter">Ruta / Destino</label>
                <input id="routeFilter" type="text" wire:model.live="routeFilter" class="form-control" placeholder="Ciudad, tramo o destino">
                <p class="mt-1 text-xs text-token-muted">Filtra por origen o destino del pedido ligado a la factura.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-token-muted">Facturado</p>
            <p class="mt-1 text-2xl font-semibold text-token">{{ \App\Support\Formatters\MoneyFormatter::pen($summary['billed'] ?? 0) }}</p>
        </div>
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-token-muted">Cobrado</p>
            <p class="mt-1 text-2xl font-semibold text-token">{{ \App\Support\Formatters\MoneyFormatter::pen($summary['collected'] ?? 0) }}</p>
        </div>
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-token-muted">Pendiente</p>
            <p class="mt-1 text-2xl font-semibold text-token">{{ \App\Support\Formatters\MoneyFormatter::pen($summary['pending'] ?? 0) }}</p>
        </div>
        <div class="surface-card p-4 shadow-sm">
            <p class="text-sm text-token-muted">Gastos operativos</p>
            <p class="mt-1 text-2xl font-semibold text-token">{{ \App\Support\Formatters\MoneyFormatter::pen($summary['expenses'] ?? 0) }}</p>
        </div>
    </div>

    <div class="surface-card overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
            <table class="table table-md">
                <thead>
                    <tr class="table-row">
                        <th class="table-header">Periodo</th>
                        <th class="table-header">Rango</th>
                        <th class="table-header">Facturado</th>
                        <th class="table-header">Cobrado</th>
                        <th class="table-header">Gastos operativos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periodRows as $row)
                        <tr class="table-row table-row-hover">
                            <td class="table-cell text-sm font-semibold text-token">{{ $row['label'] }}</td>
                            <td class="table-cell text-sm text-token-muted">{{ $row['range'] }}</td>
                            <td class="table-cell text-sm font-semibold text-token">{{ \App\Support\Formatters\MoneyFormatter::pen($row['invoiced']) }}</td>
                            <td class="table-cell text-sm font-semibold text-token">{{ \App\Support\Formatters\MoneyFormatter::pen($row['collected']) }}</td>
                            <td class="table-cell text-sm font-semibold text-token">{{ \App\Support\Formatters\MoneyFormatter::pen($row['expenses']) }}</td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="5" class="table-empty">Sin datos para el rango seleccionado</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
