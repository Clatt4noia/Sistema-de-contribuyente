<?php

$pfxFile = 'public/certs/LLAMA-PE-CERTIFICADO-DEMO-20000000001.pfx';
$pemFile = 'public/certs/certificate_20000000001.pem';
$password = '192837'; // Contraseña provista por usuario

if (!file_exists($pfxFile)) {
    die("Error: Archivo PFX no encontrado: $pfxFile\n");
}

$pfxContent = file_get_contents($pfxFile);
$certs = [];

// Intentar leer
if (!openssl_pkcs12_read($pfxContent, $certs, $password)) {
    echo "Error leyendo PFX. La contraseña '$password' podría ser incorrecta.\n";
    echo "Detalles OpenSSL: " . openssl_error_string() . "\n";
    exit(1);
}

// Extraer Clave Privada y Certificado
$privateKey = $certs['pkey'] ?? '';
$cert = $certs['cert'] ?? '';

if (!$privateKey || !$cert) {
    die("Error: No se encontró clave privada o certificado en el PFX.\n");
}

// Guardar en PEM
$pemContent = $privateKey . "\n" . $cert;
if (file_put_contents($pemFile, $pemContent)) {
    echo "¡Éxito! Certificado convertido a: $pemFile\n";
} else {
    echo "Error guardando archivo PEM.\n";
}
