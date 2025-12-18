#!/usr/bin/env php
<?php
/**
 * Script de verificación y configuración del certificado digital para SUNAT
 * 
 * Este script ayuda a verificar que el certificado digital esté correctamente
 * configurado para emitir guías de remitente (GRE-R) a SUNAT.
 */

echo "\n";
echo "═══════════════════════════════════════════════════════════════════\n";
echo "  Verificación de Certificado Digital para SUNAT (GRE-R)\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

// Cargar el autoloader de Laravel
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Obtener la configuración
$certificatePath = config('billing.certificate.path');
$passphrase = config('billing.certificate.passphrase');

echo "📋 VERIFICACIÓN DE CONFIGURACIÓN\n";
echo "─────────────────────────────────────────────────────────────────\n\n";

// 1. Verificar que las variables estén configuradas
echo "1. Variables de entorno:\n";
if (empty($certificatePath)) {
    echo "   ❌ BILLING_CERTIFICATE_PATH no está configurado en .env\n";
    echo "   💡 Agrega: BILLING_CERTIFICATE_PATH=C:/ruta/al/certificado.pfx\n\n";
    exit(1);
} else {
    echo "   ✅ BILLING_CERTIFICATE_PATH configurado\n";
    echo "      Valor: {$certificatePath}\n\n";
}

// 2. Verificar que el archivo existe
echo "2. Archivo del certificado:\n";
if (!file_exists($certificatePath)) {
    echo "   ❌ El archivo NO existe en la ruta especificada\n";
    echo "   💡 Verifica que la ruta sea correcta y el archivo exista\n\n";
    
    // Sugerir rutas alternativas
    $possiblePaths = [
        __DIR__ . '/app/billing/certificado.pfx',
        __DIR__ . '/storage/certificados/certificado.pfx',
        'C:/certificados/certificado.pfx',
    ];
    
    echo "   📁 Rutas sugeridas donde buscar el certificado:\n";
    foreach ($possiblePaths as $path) {
        $exists = file_exists($path) ? '✅ EXISTE' : '❌ No existe';
        echo "      {$exists}: {$path}\n";
    }
    echo "\n";
    exit(1);
} else {
    echo "   ✅ El archivo existe\n";
    $fileSize = filesize($certificatePath);
    echo "      Tamaño: " . number_format($fileSize) . " bytes\n";
    echo "      Última modificación: " . date('Y-m-d H:i:s', filemtime($certificatePath)) . "\n\n";
}

// 3. Verificar permisos de lectura
echo "3. Permisos de lectura:\n";
if (!is_readable($certificatePath)) {
    echo "   ❌ El archivo NO es legible por la aplicación\n";
    echo "   💡 Verifica los permisos del archivo\n\n";
    exit(1);
} else {
    echo "   ✅ El archivo es legible\n\n";
}

// 4. Verificar que se puede leer el contenido
echo "4. Contenido del certificado:\n";
$pkcs12 = @file_get_contents($certificatePath);
if ($pkcs12 === false) {
    echo "   ❌ No se pudo leer el contenido del archivo\n\n";
    exit(1);
} else {
    echo "   ✅ Contenido leído correctamente\n\n";
}

// 5. Verificar que se puede interpretar como certificado PFX
echo "5. Validación del certificado PFX:\n";
$certs = [];
$passphraseToUse = $passphrase ?: '';

if (@openssl_pkcs12_read($pkcs12, $certs, $passphraseToUse)) {
    echo "   ✅ Certificado PFX válido\n";
    
    // Mostrar información del certificado
    if (isset($certs['cert'])) {
        $certInfo = openssl_x509_parse($certs['cert']);
        echo "\n   📄 Información del certificado:\n";
        echo "      Emisor: " . ($certInfo['issuer']['CN'] ?? 'N/A') . "\n";
        echo "      Sujeto: " . ($certInfo['subject']['CN'] ?? 'N/A') . "\n";
        echo "      Válido desde: " . date('Y-m-d', $certInfo['validFrom_time_t']) . "\n";
        echo "      Válido hasta: " . date('Y-m-d', $certInfo['validTo_time_t']) . "\n";
        
        // Verificar si está vencido
        $now = time();
        if ($now < $certInfo['validFrom_time_t']) {
            echo "      ⚠️  ADVERTENCIA: El certificado aún no es válido\n";
        } elseif ($now > $certInfo['validTo_time_t']) {
            echo "      ❌ ERROR: El certificado está VENCIDO\n";
        } else {
            $daysRemaining = floor(($certInfo['validTo_time_t'] - $now) / 86400);
            echo "      ✅ Certificado vigente ({$daysRemaining} días restantes)\n";
            
            if ($daysRemaining < 30) {
                echo "      ⚠️  ADVERTENCIA: El certificado vence pronto, considera renovarlo\n";
            }
        }
    }
    echo "\n";
} else {
    echo "   ❌ No se pudo interpretar el certificado PFX\n";
    echo "   💡 Posibles causas:\n";
    echo "      - La contraseña es incorrecta\n";
    echo "      - El archivo está corrupto\n";
    echo "      - El formato no es PKCS#12 (.pfx)\n";
    
    if (empty($passphrase)) {
        echo "\n   ℹ️  Nota: No se configuró contraseña (BILLING_CERTIFICATE_PASSPHRASE)\n";
        echo "      Si el certificado tiene contraseña, agrégala en .env\n";
    }
    echo "\n";
    exit(1);
}

// 6. Verificar el servicio de firma digital
echo "6. Servicio de firma digital:\n";
try {
    $signatureService = app(\App\Domains\Billing\Services\DigitalSignatureService::class);
    echo "   ✅ Servicio de firma digital disponible\n\n";
} catch (\Exception $e) {
    echo "   ❌ Error al cargar el servicio: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Resumen final
echo "═══════════════════════════════════════════════════════════════════\n";
echo "  ✅ VERIFICACIÓN COMPLETADA EXITOSAMENTE\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "El certificado digital está correctamente configurado.\n";
echo "Ahora puedes emitir guías de remitente (GRE-R) a SUNAT.\n\n";

echo "📝 Próximos pasos:\n";
echo "   1. Navega a: Facturación → Guías de Remitente (GRE-R)\n";
echo "   2. Crea una nueva guía con todos los datos requeridos\n";
echo "   3. Guarda la guía\n";
echo "   4. Haz clic en 'Emitir'\n";
echo "   5. Verifica que el estado cambie a 'Pendiente' o 'Enviado'\n\n";

exit(0);
