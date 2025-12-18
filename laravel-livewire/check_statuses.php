<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Estados en la Base de Datos ===\n\n";

echo "Trucks:\n";
$truckStatuses = DB::table('trucks')->select('status')->distinct()->pluck('status');
foreach ($truckStatuses as $status) {
    $count = DB::table('trucks')->where('status', $status)->count();
    echo "  - '{$status}': {$count} vehículos\n";
}

echo "\nDrivers:\n";
$driverStatuses = DB::table('drivers')->select('status')->distinct()->pluck('status');
foreach ($driverStatuses as $status) {
    $count = DB::table('drivers')->where('status', $status)->count();
    echo "  - '{$status}': {$count} conductores\n";
}
