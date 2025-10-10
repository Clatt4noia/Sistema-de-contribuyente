<?php

namespace App\Services\Billing;

use DOMDocument;
use DOMXPath;
use RuntimeException;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class DigitalSignatureService
{
    public function sign(string $unsignedXml): string
    {
        $certificatePath = config('billing.certificate.path');
        $passphrase = config('billing.certificate.passphrase');

        if (! $certificatePath || ! file_exists($certificatePath)) {
            throw new RuntimeException('No se encontró el certificado digital configurado.');
        }

        $pkcs12 = file_get_contents($certificatePath);
        if ($pkcs12 === false) {
            throw new RuntimeException('No fue posible leer el certificado digital.');
        }

        $certs = [];
        if (! openssl_pkcs12_read($pkcs12, $certs, $passphrase ?: '')) {
            throw new RuntimeException('No fue posible interpretar el certificado PFX. Verifique la contraseña.');
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = false;
        $document->loadXML($unsignedXml);

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');

        $extensionContent = $xpath->query('//ext:ExtensionContent')->item(0);
        if (! $extensionContent) {
            throw new RuntimeException('El XML no contiene el nodo de extensión requerido para la firma.');
        }

        $objDSig = new XMLSecurityDSig();
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        $objDSig->addReference(
            $document->documentElement,
            XMLSecurityDSig::SHA256,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
            ['id_name' => 'ID', 'overwrite' => false]
        );

        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $objKey->loadKey($certs['pkey'], false);

        $signatureNode = $objDSig->sign($objKey);
        $signatureNode->setAttribute('Id', 'SignatureKG');
        $objDSig->add509Cert($certs['cert'], true, false, ['subjectName' => true]);

        $extensionContent->appendChild($signatureNode);

        return $document->saveXML();
    }
}
