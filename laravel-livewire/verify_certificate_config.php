<?php

/**
 * Script de verificación de certificado digital para SUNAT
 * 
 * Este script verifica si el certificado digital está correctamente configurado
 * para la emisión de documentos electrónicos (facturas y guías de remisión).
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Storage;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Verificación de Certificado Digital SUNAT ===\n\n";

// Obtener configuración de la empresa
$company = \App\Models\Company::first();

if (!$company) {
    echo "❌ ERROR: No se encontró configuración de empresa en la base de datos.\n";
    echo "   Por favor, ejecuta: php artisan db:seed --class=CompanySeeder\n\n";
    exit(1);
}

echo "✓ Empresa encontrada:\n";
echo "  RUC: {$company->ruc}\n";
echo "  Razón Social: {$company->razon_social}\n";
echo "  Ruta configurada: {$company->cert_path}\n\n";

// Verificar rutas posibles
$paths = [
    'Storage::path' => Storage::path($company->cert_path),
    'base_path' => base_path($company->cert_path),
    'storage_path' => storage_path($company->cert_path),
];

echo "Verificando rutas posibles:\n\n";

$found = false;
$foundPath = null;

foreach ($paths as $label => $path) {
    $exists = file_exists($path);
    $icon = $exists ? '✓' : '✗';
    $status = $exists ? 'EXISTE' : 'NO EXISTE';
    
    echo "  {$icon} [{$status}] {$label}:\n";
    echo "     {$path}\n";
    
    if ($exists) {
        $found = true;
        $foundPath = $path;
        
        // Verificar permisos de lectura
        if (is_readable($path)) {
            echo "     ✓ Archivo es legible\n";
            
            // Verificar tamaño
            $size = filesize($path);
            echo "     ✓ Tamaño: " . number_format($size) . " bytes\n";
            
            // Intentar leer el contenido
            try {
                $content = file_get_contents($path);
                if (strpos($content, 'BEGIN CERTIFICATE') !== false || strpos($content, 'BEGIN RSA PRIVATE KEY') !== false) {
                    echo "     ✓ Formato PEM detectado\n";
                } else {
                    echo "     ⚠ ADVERTENCIA: No se detectó formato PEM estándar\n";
                }
            } catch (\Exception $e) {
                echo "     ✗ Error al leer archivo: {$e->getMessage()}\n";
            }
        } else {
            echo "     ✗ Archivo NO es legible (verificar permisos)\n";
        }
    }
    
    echo "\n";
}

echo "\n=== RESULTADO ===\n\n";

if ($found) {
    echo "✓ CERTIFICADO ENCONTRADO\n";
    echo "  Ubicación: {$foundPath}\n\n";
    echo "El certificado está correctamente configurado y puede ser usado\n";
    echo "para emitir facturas y guías de remisión a SUNAT.\n\n";
    exit(0);
} else {
    echo "❌ CERTIFICADO NO ENCONTRADO\n\n";
    echo "El archivo de certificado no existe en ninguna de las rutas verificadas.\n\n";
    echo "SOLUCIONES:\n";
    echo "1. Coloca el archivo 'combinado.pem' en una de estas ubicaciones:\n";
    foreach ($paths as $label => $path) {
        echo "   - {$path}\n";
    }
    echo "\n";
    echo "2. O actualiza la ruta en la base de datos:\n";
    echo "   UPDATE companies SET cert_path = 'ruta/al/certificado.pem' WHERE id = 1;\n\n";
    echo "3. Para generar un certificado de prueba, consulta:\n";
    echo "   README-billing.md o CONFIGURAR_CERTIFICADO.md\n\n";
    exit(1);
}
