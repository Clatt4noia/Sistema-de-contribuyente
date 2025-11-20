<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seguimiento SUNAT</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h2>Tablero de seguimiento SUNAT</h2>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Código</th>
                <th>Serie</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th>Mensaje</th>
                <th>Emisión</th>
                <th>Último envío</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row['document_label'] }}</td>
                    <td>{{ $row['code'] }}</td>
                    <td>{{ $row['series'] }}</td>
                    <td>{{ $row['client'] ?? 'Sin cliente' }}</td>
                    <td>{{ strtoupper($row['sunat_status']) }}</td>
                    <td>{{ $row['sunat_message'] ?? 'Sin respuesta' }}</td>
                    <td>{{ optional($row['issued_at'])->format('d/m/Y') }}</td>
                    <td>{{ optional($row['last_synced_at'])->format('d/m/Y H:i') ?? 'N/D' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
