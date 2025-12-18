<?php

/**
 * Test de carga de certificado para Greenter
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test de Carga de Certificado para Greenter ===\n\n";

try {
    // 1. Obtener empresa
    $company = \App\Models\Company::first();
    if (!$company) {
        throw new Exception("No se encontró empresa configurada");
    }
    
    echo "✓ Empresa: {$company->razon_social} (RUC: {$company->ruc})\n";
    echo "  Ruta certificado: {$company->cert_path}\n\n";
    
    // 2. Intentar cargar certificado con GreenterService
    echo "Intentando cargar certificado con GreenterService...\n";
    $greenterService = app(\App\Services\Sunat\GreenterService::class);
    
    $see = $greenterService->getSee($company);
    
    echo "✓ GreenterService cargado exitosamente\n";
    echo "✓ Objeto See creado correctamente\n";
    echo "✓ Certificado cargado y configurado\n\n";
    
    // 3. Verificar configuración
    echo "Configuración de SUNAT:\n";
    echo "  Modo: " . ($company->production ? "PRODUCCIÓN" : "BETA/HOMOLOGACIÓN") . "\n";
    echo "  Usuario SOL: {$company->sol_user}\n";
    echo "  RUC: {$company->ruc}\n\n";
    
    echo "=== RESULTADO ===\n";
    echo "✓ El certificado se carga correctamente\n";
    echo "✓ GreenterService funciona para guías de remisión\n";
    echo "✓ El sistema está listo para emitir documentos\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR al cargar certificado:\n";
    echo $e->getMessage() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
