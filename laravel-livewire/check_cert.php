<?php
$cert = file_get_contents('public/certs/certificate_20000000001.pem');
if (!$cert) {
    echo "Certificate file not found.\n";
    exit(1);
}
$info = openssl_x509_parse($cert);
if (!$info) {
    echo "Failed to parse certificate.\n";
    exit(1);
}
print_r($info['subject']);
