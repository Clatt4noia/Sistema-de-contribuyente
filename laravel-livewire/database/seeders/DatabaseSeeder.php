<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\Driver;
use App\Models\Maintenance;
use App\Models\Order;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Administrador de Prueba',
            'email' => 'admin@example.com',
            'role' => UserRole::ADMIN,
        ]);

        User::factory()->create([
            'name' => 'Coordinador Logístico',
            'email' => 'logistica@example.com',
            'role' => UserRole::LOGISTICS_MANAGER,
        ]);

        $trucks = Truck::factory(8)->create();

        $drivers = Driver::factory(10)->create()->each(function (Driver $driver) {
            $driver->trainings()->createMany([
                [
                    'name' => 'Seguridad vial avanzada',
                    'provider' => 'Academia de Transporte',
                    'issued_at' => now()->subMonths(rand(1, 10)),
                    'expires_at' => now()->addMonths(rand(3, 18)),
                    'hours' => 16,
                    'status' => 'valid',
                ],
            ]);
        });

        $placeholderDocument = 'fleet-documents/demo/demo.pdf';
        if (! Storage::disk('public')->exists($placeholderDocument)) {
            Storage::disk('public')->put($placeholderDocument, 'Documento de ejemplo de la flota');
        }

        $trucks->each(function (Truck $truck) use ($placeholderDocument) {
            $truck->documents()->create([
                'document_type' => 'soat',
                'title' => 'SOAT ' . now()->format('Y'),
                'issued_at' => now()->subMonths(rand(6, 12)),
                'expires_at' => now()->addMonths(rand(-2, 6)),
                'notes' => 'Carga generada automáticamente para pruebas.',
                'file_path' => $placeholderDocument,
            ]);
        });

        $drivers->each(function (Driver $driver) use ($placeholderDocument) {
            $driver->documents()->create([
                'document_type' => 'license',
                'title' => 'Copia de licencia',
                'issued_at' => now()->subYears(3),
                'expires_at' => $driver->license_expiration,
                'notes' => 'Licencia escaneada de referencia.',
                'file_path' => $placeholderDocument,
            ]);
        });

        $orders = Order::factory(12)->create();

        Maintenance::factory(6)->make()->each(function (Maintenance $maintenance) use ($trucks) {
            $maintenance->truck_id = $trucks->random()->id;
            $maintenance->save();
        });

        Assignment::factory(15)->make()->each(function (Assignment $assignment) use ($trucks, $drivers, $orders) {
            $assignment->truck_id = $trucks->random()->id;
            $assignment->driver_id = $drivers->random()->id;
            $assignment->order_id = $orders->random()->id;
            $assignment->save();
        });
    }
}
