<?php

namespace Tests\Feature\Fleet;

use App\Enums\Fleet\MaintenanceStatus;
use App\Models\Maintenance;
use App\Models\Truck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MaintenanceSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_maintenance_updates_truck_snapshot_and_mileage(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-01-01 10:00:00'));

        $truck = Truck::factory()->create([
            'mileage' => 1000,
            'maintenance_interval_days' => 90,
            'last_maintenance_mileage' => 0,
        ]);

        Maintenance::create([
            'truck_id' => $truck->id,
            'maintenance_date' => '2025-01-10',
            'maintenance_type' => 'Preventivo',
            'cost' => 120.50,
            'odometer' => 1500,
            'status' => MaintenanceStatus::Completed->value,
            'description' => 'Mantenimiento preventivo completado.',
        ]);

        $truck->refresh();

        $this->assertSame('2025-01-10', $truck->last_maintenance?->toDateString());
        $this->assertSame('2025-04-10', $truck->next_maintenance?->toDateString());
        $this->assertSame(1500, $truck->last_maintenance_mileage);
        $this->assertSame(1500, $truck->mileage);
    }

    public function test_next_maintenance_prefers_earliest_between_interval_and_scheduled_future(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-01-01 10:00:00'));

        $truck = Truck::factory()->create([
            'mileage' => 1000,
            'maintenance_interval_days' => 90,
        ]);

        Maintenance::create([
            'truck_id' => $truck->id,
            'maintenance_date' => '2025-01-01',
            'maintenance_type' => 'Preventivo',
            'cost' => 100,
            'status' => MaintenanceStatus::Completed->value,
            'description' => 'Se completó el mantenimiento base.',
        ]);

        Maintenance::create([
            'truck_id' => $truck->id,
            'maintenance_date' => '2025-03-15',
            'maintenance_type' => 'Preventivo',
            'cost' => 0,
            'status' => MaintenanceStatus::Scheduled->value,
            'description' => 'Mantenimiento programado.',
        ]);

        $truck->refresh();

        // Intervalo: 2025-04-01 vs agenda: 2025-03-15 -> elegimos la más próxima.
        $this->assertSame('2025-03-15', $truck->next_maintenance?->toDateString());
    }
}

