<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$company = App\Models\Company::first();
$invoice = App\Models\Invoice::latest()->first();

$output = [
    'company' => [
        'ruc' => $company->ruc,
        'razon_social' => $company->razon_social,
        'sol_user' => $company->sol_user,
        'sol_pass_len' => strlen($company->sol_pass ?? ''),
        'cert_path' => $company->cert_path,
        'production' => $company->production,
        'cert_exists' => file_exists(base_path($company->cert_path)),
    ],
    'invoice' => $invoice ? [
        'id' => $invoice->id,
        'number' => $invoice->invoice_number,
        'sunat_status' => $invoice->sunat_status,
        'sunat_message' => $invoice->sunat_response_message,
    ] : null,
];

file_put_contents('sunat_check.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Guardado en sunat_check.json\n";
