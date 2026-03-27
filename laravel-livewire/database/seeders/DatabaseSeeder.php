<?php

namespace Database\Seeders;

use App\Enums\Fleet\DriverStatus;
use App\Enums\Fleet\TruckStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Catalogos Base
        $this->call([
            CompanySeeder::class,
            CargoTypeSeeder::class,
            SunatBillingCatalogSeeder::class,
            MtcReferentialRatesSeeder::class,
            UbigeoSeeder::class, // Incluye la data JSON de Ubigeos
        ]);

        // 2. Usuarios Base
        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'), // explicitly setting password
            'role' => UserRole::ADMIN,
        ]);

        User::factory()->create([
            'name' => 'Coordinador Logístico',
            'email' => 'logistica@example.com',
            'role' => UserRole::LOGISTICS_MANAGER,
        ]);

        // 3. Clientes (Remitentes/Destinatarios) - 4 entradas
        Client::factory(4)->create();

        // 4. Flota (Camiones)
        // Camión 1: Tracto Principal (Datos reales)
        Truck::factory()->create([
            'plate_number' => 'C9F813',
            'tuce_number' => '15M23039620E',
            'special_auth_issuer' => 'MTC',
            'special_auth_number' => '151908863',
            'is_secondary' => false,
            'status' => TruckStatus::Available,
        ]);
        // Camión 2: Tracto Principal (Aleatorio)
        Truck::factory()->create([
            'is_secondary' => false,
            'status' => TruckStatus::Available,
        ]);
        
        // Camión 3: Remolque Secundario (Datos reales)
        Truck::factory()->create([
            'plate_number' => 'B3H974',
            'tuce_number' => '15M22016949E',
            'special_auth_issuer' => 'MTC',
            'special_auth_number' => '15M22016949E',
            'is_secondary' => true,
            'status' => TruckStatus::Available,
        ]);
        // Camión 4: Remolque Secundario (Aleatorio)
        Truck::factory()->create([
            'is_secondary' => true,
            'status' => TruckStatus::Available,
        ]);

        // 5. Conductores
        // Conductor 1: Datos reales de prueba (DNI vinculado a la licencia Q09794946)
        // OJO: Asumiré que el DNI es 09794946 (la licencia es letra + DNI en Perú)
        Driver::factory()->create([
            'document_type' => '1',
            'document_number' => '09794946',
            'license_number' => 'Q09794946',
            'status' => DriverStatus::Active,
        ]);
        // 3 aleatorios
        Driver::factory(3)->create(['status' => DriverStatus::Active]);
    }
}
