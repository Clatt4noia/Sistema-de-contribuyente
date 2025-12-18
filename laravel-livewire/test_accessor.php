<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test de Accessor de Driver ===\n\n";

$driver = \App\Models\Driver::where('status', 'active')->first();

if ($driver) {
    echo "Driver encontrado:\n";
    echo "  ID: {$driver->id}\n";
    echo "  Nombre: {$driver->name}\n";
    echo "  Status (raw): " . DB::table('drivers')->where('id', $driver->id)->value('status') . "\n";
    echo "  Status (accessor): {$driver->status}\n";
    echo "  Comparación === 'active': " . ($driver->status === 'active' ? 'TRUE' : 'FALSE') . "\n\n";
    
    echo "Probando en blade:\n";
    echo "  \$driver->status === 'active' debería ser TRUE\n";
} else {
    echo "No se encontró ningún driver con status 'active'\n";
}

echo "\n=== Test de Truck ===\n\n";

$truck = \App\Models\Truck::where('status', 'available')->first();

if ($truck) {
    echo "Truck encontrado:\n";
    echo "  ID: {$truck->id}\n";
    echo "  Placa: {$truck->plate_number}\n";
    echo "  Status: {$truck->status}\n";
    echo "  Comparación === 'available': " . ($truck->status === 'available' ? 'TRUE' : 'FALSE') . "\n";
} else {
    echo "No se encontró ningún truck con status 'available'\n";
}
