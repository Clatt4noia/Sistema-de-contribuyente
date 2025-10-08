<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante {{ $invoice->numero_completo }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #1f2937; font-size: 12px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 24px; }
        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; font-weight: 600; font-size: 10px; }
        .badge-aceptado { background: #d1fae5; color: #047857; }
        .badge-rechazado { background: #fee2e2; color: #b91c1c; }
        .badge-observado { background: #fef3c7; color: #b45309; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th { text-align: left; background: #f8fafc; padding: 8px; font-size: 11px; border-bottom: 1px solid #e2e8f0; }
        td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        .totals { margin-top: 18px; width: 40%; margin-left: auto; }
        .totals td { padding: 6px 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1 style="font-size:20px; margin:0;">{{ config('app.name', 'Carlos Gabriel Transporte S.A.C.') }}</h1>
            <p style="margin:4px 0 0;">RUC: {{ $invoice->ruc_emisor }}</p>
            <p style="margin:2px 0 0;">{{ $invoice->client->business_name ?? 'Cliente' }}</p>
        </div>
        <div style="text-align:right;">
            <p style="font-size:14px; font-weight:700; margin:0;">{{ $invoice->numero_completo }}</p>
            <p style="margin:4px 0 0;">Emitido: {{ optional($invoice->issue_date)->format('d/m/Y') }}</p>
            <span class="badge badge-{{ $invoice->sunat_status }}">{{ ucfirst($invoice->sunat_status) }}</span>
        </div>
    </div>

    <div style="margin-bottom:12px;">
        <p style="margin:2px 0;">Cliente: <strong>{{ $invoice->client->business_name ?? 'Cliente sin razón social' }}</strong></p>
        <p style="margin:2px 0;">RUC Cliente: <strong>{{ $invoice->ruc_receptor }}</strong></p>
        <p style="margin:2px 0;">Dirección: {{ $invoice->client->address ?? 'Sin dirección registrada' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Cant.</th>
                <th>Precio unit.</th>
                <th>IGV</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item['description'] }}</td>
                    <td>{{ number_format($item['quantity'] ?? 1, 2) }}</td>
                    <td>S/ {{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                    <td>S/ {{ number_format($item['tax_amount'] ?? 0, 2) }}</td>
                    <td>S/ {{ number_format(($item['subtotal'] ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tbody>
            <tr>
                <td>Base imponible</td>
                <td style="text-align:right;">S/ {{ number_format($invoice->taxable_amount, 2) }}</td>
            </tr>
            <tr>
                <td>IGV</td>
                <td style="text-align:right;">S/ {{ number_format($invoice->tax, 2) }}</td>
            </tr>
            <tr>
                <td style="font-weight:700;">Total</td>
                <td style="text-align:right; font-weight:700;">S/ {{ number_format($invoice->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top:24px; font-size:11px; color:#475569;">Mensaje SUNAT: {{ $invoice->sunat_response_message ?? 'Pendiente de procesamiento.' }}</p>
</body>
</html>
