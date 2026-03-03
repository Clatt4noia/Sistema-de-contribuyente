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
            'name' => 'Administrador de Prueba',
            'email' => 'admin@example.com',
            'role' => UserRole::ADMIN,
        ]);

        User::factory()->create([
            'name' => 'Coordinador Logístico',
            'email' => 'logistica@example.com',
            'role' => UserRole::LOGISTICS_MANAGER,
        ]);

        // 3. Clientes (Remitentes/Destinatarios) - 5 entradas
        Client::factory(5)->create();

        // 4. Flota (Camiones) - 5 entradas
        // Aseguramos status 'available' para poder usarlos en guias
        Truck::factory(5)->create([
            'status' => TruckStatus::Available,
        ]);

        // 5. Conductores - 5 entradas
        // Aseguramos status 'active' y licencia vigente
        Driver::factory(5)->create([
            'status' => DriverStatus::Active,
        ]);
    }
}
