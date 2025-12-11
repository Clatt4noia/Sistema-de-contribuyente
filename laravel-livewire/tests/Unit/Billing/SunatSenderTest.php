<?php

namespace Tests\Unit\Billing;

use App\Domains\Billing\Services\SunatSender;
use Tests\TestCase;
use ZipArchive;

class SunatSenderTest extends TestCase
{
    public function test_it_parses_cdr_zip(): void
    {
        $xml = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <ApplicationResponse xmlns="urn:oasis:names:specification:ubl:schema:xsd:ApplicationResponse-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
            <cbc:ResponseCode>0</cbc:ResponseCode>
            <cbc:Description>Aceptado</cbc:Description>
        </ApplicationResponse>
        XML;

        $tempZip = tempnam(sys_get_temp_dir(), 'cdr');
        $zip = new ZipArchive();
        $zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('R-123.xml', $xml);
        $zip->close();

        $content = file_get_contents($tempZip);
        unlink($tempZip);

        $sender = new SunatSender('homologation');
        $result = $sender->parseCdr($content);

        $this->assertTrue($result['is_accepted']);
        $this->assertSame('0', $result['code']);
        $this->assertSame('Aceptado', $result['description']);
    }
}
