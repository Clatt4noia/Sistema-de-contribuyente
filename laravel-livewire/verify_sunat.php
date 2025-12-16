<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$invoice = App\Models\Invoice::where('sunat_status', 'aceptado')->latest()->first();

if (!$invoice) {
    echo "No se encontró factura aceptada\n";
    exit;
}

echo "=== FACTURA ACEPTADA ===\n";
echo "Número: " . $invoice->invoice_number . "\n";
echo "Estado SUNAT: " . $invoice->sunat_status . "\n";
echo "Mensaje SUNAT: " . $invoice->sunat_response_message . "\n";
echo "XML Path: " . ($invoice->xml_path ?? 'NULL') . "\n";
echo "CDR Path: " . ($invoice->cdr_path ?? 'NULL') . "\n";
echo "Hash: " . ($invoice->hash ?? 'NULL') . "\n";

// Verificar si los archivos existen
if ($invoice->xml_path && \Storage::exists($invoice->xml_path)) {
    echo "\n✅ XML existe en storage\n";
    echo "Tamaño: " . \Storage::size($invoice->xml_path) . " bytes\n";
} else {
    echo "\n❌ XML NO existe\n";
}

if ($invoice->cdr_path && \Storage::exists($invoice->cdr_path)) {
    echo "\n✅ CDR existe en storage\n";
    echo "Tamaño: " . \Storage::size($invoice->cdr_path) . " bytes\n";
    
    // Leer primeras líneas del CDR para verificar firma SUNAT
    $cdrContent = \Storage::get($invoice->cdr_path);
    if (strpos($cdrContent, 'SUNAT') !== false) {
        echo "✅ CDR contiene firma de SUNAT\n";
    }
    if (preg_match('/<cbc:ResponseCode>(\d+)<\/cbc:ResponseCode>/', $cdrContent, $matches)) {
        echo "Código de respuesta SUNAT: " . $matches[1] . "\n";
    }
    if (preg_match('/<cbc:Description>(.*?)<\/cbc:Description>/', $cdrContent, $matches)) {
        echo "Descripción: " . $matches[1] . "\n";
    }
} else {
    echo "\n❌ CDR NO existe\n";
}
