<?php

namespace Tests\Feature\Fleet;

use App\Enums\Fleet\MaintenanceStatus;
use App\Enums\Fleet\TruckStatus;
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

    public function test_scheduled_future_does_not_set_truck_status_to_maintenance(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-01-01 10:00:00'));

        $truck = Truck::factory()->create([
            'status' => TruckStatus::Available->value,
        ]);

        Maintenance::create([
            'truck_id' => $truck->id,
            'maintenance_date' => '2025-01-05',
            'maintenance_type' => 'Preventivo',
            'cost' => 0,
            'status' => MaintenanceStatus::Scheduled->value,
            'description' => 'Mantenimiento programado a futuro.',
        ]);

        $truck->refresh();

        $this->assertSame(TruckStatus::Available, $truck->status);
    }

    public function test_in_progress_sets_truck_status_to_maintenance(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-01-01 10:00:00'));

        $truck = Truck::factory()->create([
            'status' => TruckStatus::Available->value,
        ]);

        Maintenance::create([
            'truck_id' => $truck->id,
            'maintenance_date' => '2025-01-05',
            'maintenance_type' => 'Correctivo',
            'cost' => 0,
            'status' => MaintenanceStatus::InProgress->value,
            'description' => 'Mantenimiento en progreso.',
        ]);

        $truck->refresh();

        $this->assertSame(TruckStatus::Maintenance, $truck->status);
    }

    public function test_scheduled_due_or_overdue_can_set_truck_status_to_maintenance(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-01-10 10:00:00'));

        $truck = Truck::factory()->create([
            'status' => TruckStatus::Available->value,
        ]);

        Maintenance::create([
            'truck_id' => $truck->id,
            'maintenance_date' => '2025-01-10',
            'maintenance_type' => 'Preventivo',
            'cost' => 0,
            'status' => MaintenanceStatus::Scheduled->value,
            'description' => 'Mantenimiento programado para hoy.',
        ]);

        $truck->refresh();

        $this->assertSame(TruckStatus::Maintenance, $truck->status);
    }

    public function test_alerts_use_derived_dates_even_if_snapshot_is_stale(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-01-01 10:00:00'));

        $truck = Truck::factory()->create([
            'maintenance_interval_days' => 90,
            'mileage' => 1000,
            'last_maintenance' => '2024-01-01',
            'next_maintenance' => '2024-04-01',
        ]);

        Maintenance::create([
            'truck_id' => $truck->id,
            'maintenance_date' => '2024-12-15',
            'maintenance_type' => 'Preventivo',
            'cost' => 100,
            'status' => MaintenanceStatus::Completed->value,
            'description' => 'Mantenimiento reciente (fuente de verdad).',
        ]);

        Maintenance::create([
            'truck_id' => $truck->id,
            'maintenance_date' => '2025-01-10',
            'maintenance_type' => 'Preventivo',
            'cost' => 0,
            'status' => MaintenanceStatus::Scheduled->value,
            'description' => 'Próximo mantenimiento programado.',
        ]);

        // Simulamos un snapshot viejo (p.ej. datos legacy) para probar que las alertas usan "derived".
        $truck->last_maintenance = Carbon::parse('2024-01-01');
        $truck->next_maintenance = Carbon::parse('2024-04-01');
        $truck->saveQuietly();
        $truck->refresh();

        $this->assertFalse($truck->requiresMaintenanceAlert());
        $this->assertSame('warning', $truck->maintenanceAlertLevel());
        $this->assertSame('2025-01-10', $truck->next_maintenance_derived?->toDateString());
    }
}
