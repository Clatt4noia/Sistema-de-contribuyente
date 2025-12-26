<?php

namespace Tests\Unit\Billing;

use App\Domains\Billing\Services\DigitalSignatureService;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DigitalSignatureServiceTest extends TestCase
{
    public function test_it_signs_xml_using_pfx_certificate(): void
    {
        if (! function_exists('openssl_pkey_new')) {
            $this->markTestSkipped('OpenSSL extension is not available.');
        }

        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($privateKey === false) {
            $this->markTestSkipped('Unable to generate private key with OpenSSL.');
        }

        $csr = openssl_csr_new([
            'commonName' => 'Test Cert',
            'organizationName' => 'Carlos Gabriel Transporte S.A.C.',
            'countryName' => 'PE',
        ], $privateKey);

        if ($csr === false) {
            $this->markTestSkipped('Unable to generate CSR with OpenSSL.');
        }

        $certificate = openssl_csr_sign($csr, null, $privateKey, 365);

        if ($certificate === false) {
            $this->markTestSkipped('Unable to self-sign CSR with OpenSSL.');
        }

        $pkcs12 = null;
        if (! openssl_pkcs12_export($certificate, $pkcs12, $privateKey, 'secret')) {
            $this->markTestSkipped('Unable to export PKCS#12 bundle with OpenSSL.');
        }

        $path = tempnam(sys_get_temp_dir(), 'pfx');
        file_put_contents($path, $pkcs12);

        Config::set('billing.certificate.path', $path);
        Config::set('billing.certificate.passphrase', 'secret');

        $service = new DigitalSignatureService();

        $xml = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
            <ext:UBLExtensions>
                <ext:UBLExtension>
                    <ext:ExtensionContent></ext:ExtensionContent>
                </ext:UBLExtension>
            </ext:UBLExtensions>
        </Invoice>
        XML;

        $signed = $service->sign($xml);

        $this->assertStringContainsString('SignatureKG', $signed);
        $this->assertStringContainsString('<ds:Signature', $signed);

        unlink($path);
    }
}
