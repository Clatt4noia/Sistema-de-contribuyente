<?php

namespace Tests\Unit\Billing;

use App\Models\Invoice;
use App\Services\Billing\SunatInvoiceBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SunatInvoiceBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_valid_ubl_structure(): void
    {
        $invoice = Invoice::factory()->create();
        $builder = new SunatInvoiceBuilder();

        $xml = $builder->build($invoice, [
            [
                'description' => 'Servicio de transporte terrestre',
                'quantity' => 1,
                'unit_price' => 1000,
                'tax_percentage' => 18,
                'tax_amount' => 180,
                'taxable_amount' => 1000,
                'total' => 1000,
            ],
        ], [
            'ruc' => $invoice->ruc_emisor,
            'legal_name' => 'Carlos Gabriel Transporte S.A.C.',
        ], [
            'ruc' => $invoice->ruc_receptor,
            'name' => 'Cliente Demo S.A.C.',
        ]);

        $this->assertStringContainsString('<cbc:UBLVersionID>2.1</cbc:UBLVersionID>', $xml);
        $this->assertStringContainsString($invoice->series, $xml);
        $this->assertStringContainsString($invoice->correlative, $xml);
        $this->assertStringContainsString($invoice->ruc_emisor, $xml);
        $this->assertStringContainsString($invoice->ruc_receptor, $xml);
    }
}
