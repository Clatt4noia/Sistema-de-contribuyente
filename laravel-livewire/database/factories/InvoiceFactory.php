<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'invoice_number' => $this->faker->unique()->numerify('F001-####'),
            'document_type' => '01',
            'series' => 'F001',
            'correlative' => $this->faker->numerify('0000###'),
            'issue_date' => now(),
            'due_date' => now()->addDays(15),
            'subtotal' => 1000,
            'taxable_amount' => 1000,
            'tax' => 180,
            'total' => 1180,
            'status' => 'issued',
            'ruc_emisor' => '20123456789',
            'ruc_receptor' => '10456789012',
            'currency' => 'PEN',
            'sunat_status' => 'pendiente',
            'metadata' => ['items' => []],
        ];
    }
}
