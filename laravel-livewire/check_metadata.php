<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$invoice = App\Models\Invoice::latest()->first();

echo "Invoice ID: " . $invoice->id . "\n";
echo "Metadata: " . json_encode($invoice->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
echo "Operation Type: " . ($invoice->metadata['operation_type'] ?? 'NULL') . "\n";
