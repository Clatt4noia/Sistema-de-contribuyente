<?php

namespace Tests\Unit\Services\Billing;

use App\Models\Client;
use App\Models\Driver;
use App\Models\TransportGuide;
use App\Models\TransportGuideItem;
use App\Models\Truck;
use App\Services\Billing\DigitalSignatureService;
use App\Services\Billing\TransportGuideIssuer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class TransportGuideIssuerTest extends TestCase
{
    use RefreshDatabase;

    protected function buildDraftGuide(): TransportGuide
    {
        $client = Client::factory()->create([
            'document_number' => '20123456789',
        ]);

        $truck = Truck::factory()->create([
            'status' => 'available',
            'plate_number' => 'ABC-123',
        ]);

        $driver = Driver::factory()->create([
            'status' => 'active',
            'license_expiration' => now()->addMonths(3),
            'document_number' => '87654321',
        ]);

        $guide = TransportGuide::create([
            'type' => TransportGuide::TYPE_TRANSPORTISTA,
            'series' => 'V001',
            'correlative' => 1,
            'full_code' => 'V001-00000001',
            'issue_date' => now()->toDateString(),
            'issue_time' => '08:00:00',
            'document_type_code' => TransportGuide::DOCUMENT_TYPE_GRE_TRANSPORTISTA,
            'client_id' => $client->id,
            'remitente_document_type' => '6',
            'remitente_document_number' => '20123456789',
            'remitente_ruc' => '20123456789',
            'remitente_name' => 'Cliente Uno',
            'destinatario_document_type' => '6',
            'destinatario_document_number' => '20123456789',
            'destinatario_name' => 'Cliente Uno',
            'transportista_ruc' => '20123456789',
            'transportista_name' => 'Transportes Demo',
            'truck_id' => $truck->id,
            'driver_id' => $driver->id,
            'driver_document_number' => '87654321',
            'driver_document_type' => '1',
            'driver_name' => $driver->name,
            'driver_license_number' => $driver->license_number,
            'transfer_reason_code' => '01',
            'transport_mode_code' => '01',
            'scheduled_transshipment' => false,
            'start_transport_date' => now()->toDateString(),
            'gross_weight' => 1.500,
            'gross_weight_unit' => 'KGM',
            'origin_ubigeo' => '150101',
            'origin_address' => 'Av. Lima 123',
            'destination_ubigeo' => '150102',
            'destination_address' => 'Av. Peru 456',
            'sunat_status' => TransportGuide::STATUS_DRAFT,
        ]);

        TransportGuideItem::create([
            'transport_guide_id' => $guide->id,
            'description' => 'Pallets de prueba',
            'unit_of_measure' => 'NIU',
            'quantity' => 2,
            'weight' => 1.5,
        ]);

        return $guide;
    }

    public function test_issues_and_stores_signed_transport_guide(): void
    {
        Storage::fake('local');
        config([
            'billing.storage.disk_xml_cdr' => 'local',
            'billing.storage.xml_directory' => 'xml-tests',
        ]);

        $signature = Mockery::mock(DigitalSignatureService::class);
        $signature->shouldReceive('sign')->once()->andReturn('<signed-xml />');

        $issuer = new TransportGuideIssuer($signature);
        $guide = $issuer->issue($this->buildDraftGuide());

        $this->assertNotNull($guide->xml_path);
        $this->assertEquals(TransportGuide::STATUS_PENDING, $guide->sunat_status);
        $this->assertNotNull($guide->sent_at);
        $this->assertStringContainsString('Documento firmado', $guide->sunat_notes);
        Storage::disk('local')->assertExists($guide->xml_path);
        $this->assertStringContainsString('<signed-xml', Storage::disk('local')->get($guide->xml_path));
    }

    public function test_throws_exception_when_truck_in_maintenance(): void
    {
        $guide = $this->buildDraftGuide();
        $guide->truck->update(['status' => 'maintenance']);

        $issuer = new TransportGuideIssuer(Mockery::mock(DigitalSignatureService::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('El camión seleccionado no está disponible por mantenimiento.');

        $issuer->issue($guide);
    }
}
