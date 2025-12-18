<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$guide = DB::table('transport_guides')->where('id', 10)->first();

if ($guide) {
    echo "=== Guía ID 10 (Más reciente) ===\n\n";
    echo "Código: {$guide->series}-{$guide->correlative}\n";
    echo "Estado SUNAT: {$guide->sunat_status}\n";
    echo "Fecha actualización: {$guide->updated_at}\n";
    echo "\n=== Error Completo ===\n";
    echo $guide->sunat_notes . "\n\n";
    
    // Verificar si el error tiene las rutas verificadas
    if (strpos($guide->sunat_notes, 'Rutas verificadas') !== false) {
        echo "✓ El error muestra las rutas verificadas (código nuevo)\n";
    } else {
        echo "✗ El error NO muestra las rutas (código viejo)\n";
    }
}
