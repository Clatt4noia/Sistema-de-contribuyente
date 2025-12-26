<?php

namespace Database\Seeders;

use App\Enums\Documents\DocumentComputedStatus;
use App\Enums\Fleet\AssignmentStatus;
use App\Enums\Fleet\DriverStatus;
use App\Enums\Fleet\MaintenanceStatus;
use App\Enums\Fleet\TruckStatus;
use App\Enums\Orders\OrderStatus;
use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\Driver;
use App\Models\Maintenance;
use App\Models\Order;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            CargoTypeSeeder::class,
            SunatBillingCatalogSeeder::class,
            MtcReferentialRatesSeeder::class,
        ]);

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

        $placeholderPath = $this->ensureDemoPdf('fleet-documents/demo/demo.pdf');

        $trucks = Truck::factory(10)->create([
            'status' => TruckStatus::Available,
        ]);

        $drivers = Driver::factory(10)->create([
            'status' => DriverStatus::Active,
        ]);

        $drivers->each(function (Driver $driver) {
            $driver->trainings()->createMany([
                [
                    'name' => 'Seguridad vial avanzada',
                    'provider' => 'Academia de Transporte',
                    'issued_at' => now()->subMonths(random_int(1, 10)),
                    'expires_at' => now()->addMonths(random_int(3, 18)),
                    'hours' => 16,
                    'status' => 'valid',
                ],
            ]);
        });

        $this->seedDocuments($drivers, $trucks, $placeholderPath);

        $orders = Order::factory(15)->create();

        $cancelledOrders = $orders->shuffle()->take(3);
        $cancelledOrders->each(fn (Order $order) => $order->update(['status' => OrderStatus::Cancelled]));

        $assignableOrders = $orders
            ->reject(fn (Order $order) => $order->status === OrderStatus::Cancelled)
            ->values();

        DB::transaction(function () use ($assignableOrders, $trucks, $drivers) {
            $selectedOrders = $assignableOrders->shuffle()->take(10);

            foreach ($selectedOrders as $order) {
                $assignmentStatus = match ($order->status) {
                    OrderStatus::Pending => AssignmentStatus::Scheduled,
                    OrderStatus::EnRoute => AssignmentStatus::InProgress,
                    OrderStatus::Delivered => AssignmentStatus::Completed,
                    default => AssignmentStatus::Scheduled,
                };

                $start = $order->pickup_date instanceof Carbon ? $order->pickup_date : now()->subDays(random_int(1, 3));
                $end = $assignmentStatus === AssignmentStatus::Completed
                    ? ($order->delivery_date instanceof Carbon ? $order->delivery_date : $start->copy()->addDays(random_int(1, 3)))
                    : null;

                $truck = $trucks->random();
                $eligibleDrivers = $drivers->filter(fn (Driver $candidate) => $candidate->license_expiration?->endOfDay()->greaterThanOrEqualTo($start));
                $driver = $eligibleDrivers->isNotEmpty() ? $eligibleDrivers->random() : $drivers->random();

                Assignment::create([
                    'truck_id' => $truck->id,
                    'driver_id' => $driver->id,
                    'order_id' => $order->id,
                    'start_date' => $start,
                    'end_date' => $end,
                    'status' => $assignmentStatus,
                    'description' => 'Asignación de prueba para ' . $order->reference,
                    'notes' => null,
                ]);
            }

            $this->syncResourceStatuses();
        });

        $this->seedMaintenances($trucks);
        $this->syncResourceStatuses();
    }

    private function seedDocuments($drivers, $trucks, string $placeholderPath): void
    {
        $expiringDays = (int) config('documents.expiring_days', 30);

        $distribution = array_merge(
            array_fill(0, 12, DocumentComputedStatus::VALID),
            array_fill(0, 5, DocumentComputedStatus::EXPIRING),
            array_fill(0, 3, DocumentComputedStatus::EXPIRED),
        );

        shuffle($distribution);

        $driverStatuses = array_slice($distribution, 0, $drivers->count());
        $truckStatuses = array_slice($distribution, $drivers->count(), $trucks->count());

        foreach ($drivers as $index => $driver) {
            $status = $driverStatuses[$index] ?? DocumentComputedStatus::VALID;
            $expiresAt = $this->expiresAtFor($status, $expiringDays);
            $issuedAt = $expiresAt->copy()->subDays(random_int(30, 365));

            $driver->forceFill(['license_expiration' => $expiresAt])->save();

            $driver->documents()->create([
                'document_type' => 'license',
                'title' => 'Copia de licencia',
                'issued_at' => $issuedAt,
                'expires_at' => $expiresAt,
                'notes' => 'Documento generado automáticamente para demo.',
                'file_path' => $placeholderPath,
            ]);
        }

        foreach ($trucks as $index => $truck) {
            $status = $truckStatuses[$index] ?? DocumentComputedStatus::VALID;
            $expiresAt = $this->expiresAtFor($status, $expiringDays);
            $issuedAt = $expiresAt->copy()->subDays(random_int(30, 365));

            $truck->documents()->create([
                'document_type' => 'soat',
                'title' => 'SOAT ' . now()->format('Y'),
                'issued_at' => $issuedAt,
                'expires_at' => $expiresAt,
                'notes' => 'Documento generado automáticamente para demo.',
                'file_path' => $placeholderPath,
            ]);
        }
    }

    private function seedMaintenances($trucks): void
    {
        $trucksToMaintain = $trucks->shuffle()->take(6);

        foreach ($trucksToMaintain as $index => $truck) {
            $status = match (true) {
                $index === 0 => MaintenanceStatus::InProgress,
                $index <= 2 => MaintenanceStatus::Scheduled,
                default => MaintenanceStatus::Completed,
            };

            $date = match ($status) {
                MaintenanceStatus::Completed => now()->subDays(random_int(5, 30)),
                default => now()->addDays(random_int(1, 20)),
            };

            Maintenance::create([
                'truck_id' => $truck->id,
                'maintenance_date' => $date,
                'maintenance_type' => 'Preventivo',
                'cost' => random_int(300, 2500),
                'odometer' => $truck->mileage,
                'status' => $status,
                'description' => 'Mantenimiento de demo.',
                'notes' => null,
            ]);
        }
    }

    private function syncResourceStatuses(): void
    {
        Truck::query()->update(['status' => TruckStatus::Available->value]);
        Driver::query()->update(['status' => DriverStatus::Active->value]);

        $activeAssignments = Assignment::query()
            ->whereIn('status', [AssignmentStatus::Scheduled->value, AssignmentStatus::InProgress->value])
            ->get(['truck_id', 'driver_id']);

        foreach ($activeAssignments as $assignment) {
            if ($assignment->truck_id) {
                Truck::whereKey($assignment->truck_id)->update(['status' => TruckStatus::InUse->value]);
            }

            if ($assignment->driver_id) {
                Driver::whereKey($assignment->driver_id)->update(['status' => DriverStatus::Assigned->value]);
            }
        }

        $trucksInMaintenance = Maintenance::query()
            ->whereIn('status', [MaintenanceStatus::Scheduled->value, MaintenanceStatus::InProgress->value])
            ->pluck('truck_id')
            ->filter()
            ->unique()
            ->all();

        if (! empty($trucksInMaintenance)) {
            Truck::whereIn('id', $trucksInMaintenance)->update(['status' => TruckStatus::Maintenance->value]);
        }
    }

    private function ensureDemoPdf(string $relativePath): string
    {
        if (Storage::disk('public')->exists($relativePath)) {
            return $relativePath;
        }

        $text = 'Fleet demo PDF';
        $contentStream = "BT\n/F1 18 Tf\n50 150 Td\n(" . str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text) . ") Tj\nET\n";

        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n",
            "4 0 obj\n<< /Length " . strlen($contentStream) . " >>\nstream\n{$contentStream}\nendstream\nendobj\n",
            "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
        ];

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[$index + 1] = strlen($pdf);
            $pdf .= $object;
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF\n";

        Storage::disk('public')->put($relativePath, $pdf);

        return $relativePath;
    }

    private function expiresAtFor(DocumentComputedStatus $status, int $expiringDays): Carbon
    {
        return match ($status) {
            DocumentComputedStatus::EXPIRED => now()->subDays(random_int(1, 180)),
            DocumentComputedStatus::EXPIRING => now()->addDays(random_int(1, max($expiringDays, 1))),
            DocumentComputedStatus::VALID => now()->addDays(random_int($expiringDays + 1, 365)),
        };
    }
}
