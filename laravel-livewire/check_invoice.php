<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$invoice = App\Models\Invoice::with('details')->latest()->first();

$data = [
    'invoice' => [
        'id' => $invoice->id,
        'number' => $invoice->invoice_number,
        'subtotal' => (float) $invoice->subtotal,
        'tax' => (float) $invoice->tax,
        'total' => (float) $invoice->total,
        'taxable_amount' => (float) $invoice->taxable_amount,
    ],
    'details' => $invoice->details->map(fn($d) => [
        'description' => $d->description,
        'quantity' => (float) $d->quantity,
        'unit_price' => (float) $d->unit_price,
        'taxable_amount' => (float) $d->taxable_amount,
        'tax_amount' => (float) $d->tax_amount,
        'tax_percentage' => (float) $d->tax_percentage,
        'total' => (float) $d->total,
    ])->toArray(),
];

file_put_contents('invoice_data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Guardado en invoice_data.json\n";
