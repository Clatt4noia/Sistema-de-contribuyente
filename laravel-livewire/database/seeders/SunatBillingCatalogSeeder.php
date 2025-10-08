<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SunatBillingCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sunat_document_types')->upsert([
            ['code' => '01', 'description' => 'Factura electrónica', 'sunat_name' => 'Factura'],
            ['code' => '03', 'description' => 'Boleta de venta electrónica', 'sunat_name' => 'Boleta'],
            ['code' => '07', 'description' => 'Nota de crédito electrónica', 'sunat_name' => 'Nota de crédito'],
            ['code' => '08', 'description' => 'Nota de débito electrónica', 'sunat_name' => 'Nota de débito'],
        ], ['code'], ['description', 'sunat_name']);

        DB::table('sunat_tax_rates')->upsert([
            ['code' => '1000', 'description' => 'IGV Impuesto General a las Ventas', 'rate' => 18.00, 'type' => 'IGV'],
            ['code' => '1016', 'description' => 'IGV - Tasa 0% exportación', 'rate' => 0.00, 'type' => 'IGV'],
            ['code' => '2000', 'description' => 'ISC Impuesto Selectivo al Consumo', 'rate' => 0.00, 'type' => 'ISC'],
        ], ['code', 'type'], ['description', 'rate']);

        DB::table('sunat_error_codes')->upsert([
            ['code' => '0100', 'category' => 'Validación', 'message' => 'Error en formato de RUC.', 'resolution' => 'Verifique que el RUC del emisor y receptor tenga 11 dígitos.'],
            ['code' => '1033', 'category' => 'Firma', 'message' => 'Certificado digital inválido.', 'resolution' => 'Reemplace el certificado o revise la contraseña configurada.'],
            ['code' => '2001', 'category' => 'Proceso', 'message' => 'Documento duplicado.', 'resolution' => 'Revise que la serie y número no hayan sido enviados previamente.'],
        ], ['code'], ['category', 'message', 'resolution']);
    }
}
