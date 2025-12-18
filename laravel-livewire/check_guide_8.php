<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$guide = DB::table('transport_guides')->where('id', 8)->first();

if ($guide) {
    echo "Guía ID: {$guide->id}\n";
    echo "Código: {$guide->series}-{$guide->correlative}\n";
    echo "Estado SUNAT: {$guide->sunat_status}\n";
    echo "Fecha actualización: {$guide->updated_at}\n";
    echo "\nError completo:\n";
    echo $guide->sunat_notes . "\n";
}
