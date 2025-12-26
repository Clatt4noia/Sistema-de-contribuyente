<?php

namespace Tests\Unit\Services\Logistics;

use App\Enums\Fleet\DriverStatus;
use App\Enums\Fleet\TruckStatus;
use App\Models\Assignment;
use App\Models\CargoType;
use App\Models\Driver;
use App\Models\DriverSchedule;
use App\Models\Order;
use App\Models\Truck;
use App\Services\Logistics\OrderAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function createAvailableDriverWithSchedule(int $weekday, string $start, string $end): Driver
    {
        $driver = Driver::factory()->create([
            'status' => 'active',
            'license_expiration' => now()->addMonths(3),
        ]);

        DriverSchedule::create([
            'driver_id' => $driver->id,
            'day_of_week' => $weekday,
            'start_time' => $start,
            'end_time' => $end,
        ]);

        return $driver;
    }

    public function test_assigns_resources_matching_capacity_and_schedule(): void
    {
        $cargoType = CargoType::create([
            'name' => 'Alimentos',
            'code' => 'ALM',
        ]);

        $order = Order::factory()->create([
            'cargo_type_id' => $cargoType->id,
            'pickup_date' => Carbon::parse('2024-01-09 09:00:00'),
            'cargo_weight_kg' => 8000,
        ]);

        $preferredTruck = Truck::factory()->create([
            'status' => 'available',
            'capacity' => 10000,
            'mileage' => 5000,
        ]);
        $preferredTruck->cargoTypes()->attach($cargoType);

        $otherTruck = Truck::factory()->create([
            'status' => 'available',
            'capacity' => 12000,
            'mileage' => 9000,
        ]);
        $otherTruck->cargoTypes()->attach($cargoType);

        $driver = $this->createAvailableDriverWithSchedule(2, '08:00', '18:00');
        $service = new OrderAssignmentService();

        $assignment = $service->assignBestResources($order);

        $this->assertInstanceOf(Assignment::class, $assignment);
        $this->assertEquals($order->id, $assignment->order_id);
        $this->assertEquals($preferredTruck->id, $assignment->truck_id);
        $this->assertEquals($driver->id, $assignment->driver_id);
        $this->assertEquals(TruckStatus::Reserved, $preferredTruck->fresh()->status);
        $this->assertEquals(DriverStatus::Assigned, $driver->fresh()->status);
    }

    public function test_returns_null_when_no_driver_available_for_pickup_window(): void
    {
        $order = Order::factory()->create([
            'pickup_date' => Carbon::parse('2024-01-11 07:30:00'),
        ]);

        Truck::factory()->create([
            'status' => 'available',
            'capacity' => 7000,
        ]);

        // Driver works later in the day and should not be assigned.
        $this->createAvailableDriverWithSchedule(4, '12:00', '16:00');

        $service = new OrderAssignmentService();

        $assignment = $service->assignBestResources($order);

        $this->assertNull($assignment);
        $this->assertDatabaseCount('assignments', 0);
    }
}
