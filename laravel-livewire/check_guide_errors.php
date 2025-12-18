<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Últimas guías con error ===\n\n";

$guides = DB::table('transport_guides')
    ->where('sunat_status', 'error')
    ->orderBy('id', 'desc')
    ->limit(3)
    ->get(['id', 'series', 'correlative', 'sunat_notes', 'updated_at']);

foreach ($guides as $guide) {
    echo "ID: {$guide->id}\n";
    echo "Código: {$guide->series}-{$guide->correlative}\n";
    echo "Fecha: {$guide->updated_at}\n";
    echo "Error: {$guide->sunat_notes}\n";
    echo str_repeat('-', 80) . "\n\n";
}
