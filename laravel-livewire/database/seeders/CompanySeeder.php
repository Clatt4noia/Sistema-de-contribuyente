<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // Datos oficiales de prueba SUNAT (Entorno BETA)
        // RUC: 20123456789 (RUC ficticio aceptado en beta con usuarios beta)
        // O usamos el clásico 20000000001 de los ejemplos de Greenter si usamos el endpoint de demo, 
        // pero Greenter suele usar 20123456789 para pruebas de integración o mocks.
        // Usaremos: 20000000001 (RUC Demo de Greenter / SUNAT Beta legacy)
        // Usuario: MODDATOS
        // Clave: MODDATOS
        
        Company::create([
            'ruc' => '20000000001',
            'razon_social' => 'EMPRESA DE PRUEBA S.A.C.',
            'nombre_comercial' => 'DEMO STORE',
            'address' => 'AV. DEMO 123 LIMA - LIMA - LIMA',
            'ubigeo' => '150101',
            'sol_user' => 'MODDATOS',
            'sol_pass' => 'MODDATOS',
            'cert_path' => 'certificates/certificate.pem', // Se debe colocar un certificado dummy aquí o Greenter usará uno por defecto si se configura
            'production' => false,
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
        ]);
    }
}
