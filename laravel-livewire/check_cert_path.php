<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$company = App\Models\Company::first();
echo "RUC: {$company->ruc}\n";
echo "Cert Path: {$company->cert_path}\n";
echo "Production: " . ($company->production ? 'YES' : 'NO') . "\n";
