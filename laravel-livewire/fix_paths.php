<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$invoice = App\Models\Invoice::where('invoice_number', 'F001-00000001')->first();

if (!$invoice) {
    echo "Factura no encontrada\n";
    exit;
}

$invoice->xml_path = 'xml/20604342351-01-F001-00000001.xml';
$invoice->cdr_path = 'cdr/R-20604342351-01-F001-00000001.zip';
$invoice->save();

echo "✅ Paths actualizados:\n";
echo "XML: " . $invoice->xml_path . "\n";
echo "CDR: " . $invoice->cdr_path . "\n";

// Verificar que existan
if (\Storage::disk('public')->exists($invoice->xml_path)) {
    echo "✅ XML existe en storage\n";
} else {
    echo "❌ XML NO existe\n";
}

if (\Storage::disk('public')->exists($invoice->cdr_path)) {
    echo "✅ CDR existe en storage\n";
} else {
    echo "❌ CDR NO existe\n";
}
