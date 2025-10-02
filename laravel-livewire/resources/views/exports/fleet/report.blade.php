<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de flota</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 20px; margin-bottom: 10px; }
        h2 { font-size: 16px; margin-top: 20px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
        .grid { display: flex; gap: 12px; flex-wrap: wrap; }
        .card { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; flex: 1 1 200px; }
        .muted { color: #6b7280; font-size: 12px; margin: 0; }
        .value { font-size: 18px; font-weight: bold; margin: 0; }
    </style>
</head>
<body>
    <h1>Reporte de flota</h1>
    <p class="muted">Generado el {{ now()->format('d/m/Y H:i') }}</p>

    <div class="grid">
        <div class="card">
            <p class="muted">Camiones disponibles</p>
            <p class="value">{{ $truckTotals['available'] ?? 0 }}</p>
        </div>
        <div class="card">
            <p class="muted">Camiones en uso</p>
            <p class="value">{{ $truckTotals['in_use'] ?? 0 }}</p>
        </div>
        <div class="card">
            <p class="muted">Camiones en mantenimiento</p>
            <p class="value">{{ $truckTotals['maintenance'] ?? 0 }}</p>
        </div>
        <div class="card">
            <p class="muted">Pedidos activos</p>
            <p class="value">{{ ($orderTotals['pending'] ?? 0) + ($orderTotals['en_route'] ?? 0) }}</p>
        </div>
    </div>

    <h2>Conductores</h2>
    <table>
        <thead>
            <tr>
                <th>Estado</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($driverTotals as $status => $total)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $status)) }}</td>
                    <td>{{ $total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Asignaciones</h2>
    <table>
        <thead>
            <tr>
                <th>Estado</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($assignmentsByStatus as $status => $total)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $status)) }}</td>
                    <td>{{ $total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Mantenimientos próximos</h2>
    <table>
        <thead>
            <tr>
                <th>Vehículo</th>
                <th>Fecha</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($upcomingMaintenance as $maintenance)
                <tr>
                    <td>{{ $maintenance->truck->plate_number }}</td>
                    <td>{{ $maintenance->maintenance_date->format('d/m/Y') }}</td>
                    <td>{{ $maintenance->maintenance_type }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Sin mantenimientos programados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Top conductores del mes</h2>
    <table>
        <thead>
            <tr>
                <th>Conductor</th>
                <th>Asignaciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($topDrivers as $driver)
                <tr>
                    <td>{{ $driver->full_name }}</td>
                    <td>{{ $driver->assignments_count }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">Sin registros.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Licencias próximas a vencer</h2>
    <table>
        <thead>
            <tr>
                <th>Conductor</th>
                <th>Vence</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($licenseAlerts as $alert)
                <tr>
                    <td>{{ $alert->full_name }}</td>
                    <td>{{ $alert->license_expiration->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">Sin alertas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
