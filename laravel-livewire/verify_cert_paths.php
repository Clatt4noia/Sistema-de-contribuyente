<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Storage;

$company = App\Models\Company::first();
$certPath = $company->cert_path;

echo "=== Verificación de Certificado ===\n\n";
echo "Ruta configurada: {$certPath}\n\n";

$paths = [
    'Storage::path' => Storage::path($certPath),
    'base_path' => base_path($certPath),
    'storage_path' => storage_path($certPath),
];

echo "Verificando rutas:\n\n";
$found = false;

foreach ($paths as $label => $path) {
    $exists = file_exists($path);
    echo "{$label}:\n";
    echo "  Ruta: {$path}\n";
    echo "  Existe: " . ($exists ? 'SÍ' : 'NO') . "\n";
    
    if ($exists) {
        $found = true;
        echo "  Tamaño: " . filesize($path) . " bytes\n";
        echo "  Legible: " . (is_readable($path) ? 'SÍ' : 'NO') . "\n";
    }
    echo "\n";
}

if (!$found) {
    echo "❌ CERTIFICADO NO ENCONTRADO EN NINGUNA RUTA\n";
    exit(1);
} else {
    echo "✓ Certificado encontrado\n";
}
