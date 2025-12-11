<?php

namespace Tests\Feature\Livewire;

use App\Enums\UserRole;
use App\Domains\Fleet\Livewire\AssignmentForm;
use App\Models\Assignment;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Truck;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssignmentFormTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsFleetManager(): User
    {
        $user = User::factory()->create([
            'role' => UserRole::FLEET_MANAGER,
        ]);

        $this->actingAs($user);

        return $user;
    }

    protected function createDriver(array $attributes = []): Driver
    {
        $driver = Driver::factory()->create(array_merge([
            'license_expiration' => now()->addMonths(6),
            'status' => 'active',
        ], $attributes));

        $driver->trainings()->create([
            'name' => 'Seguridad vial',
            'status' => 'valid',
            'issued_at' => now()->subMonths(2),
            'expires_at' => now()->addMonths(6),
        ]);

        return $driver;
    }

    public function test_cannot_assign_truck_in_maintenance(): void
    {
        $this->actingAsFleetManager();

        $truck = Truck::factory()->create([
            'status' => 'maintenance',
        ]);

        $driver = $this->createDriver();
        $order = Order::factory()->create();

        Livewire::test(AssignmentForm::class)
            ->set('form.order_id', $order->id)
            ->set('form.truck_id', $truck->id)
            ->set('form.driver_id', $driver->id)
            ->set('form.start_date', Carbon::now()->addDay()->format('Y-m-d\TH:i'))
            ->set('form.end_date', Carbon::now()->addDays(2)->format('Y-m-d\TH:i'))
            ->set('form.status', 'scheduled')
            ->set('form.description', 'Ruta de prueba')
            ->set('mode', 'manual')
            ->call('save')
            ->assertHasErrors(['form.truck_id']);
    }

    public function test_cannot_assign_driver_with_expired_license(): void
    {
        $this->actingAsFleetManager();

        $truck = Truck::factory()->create([
            'status' => 'available',
        ]);

        $driver = $this->createDriver([
            'license_expiration' => now()->subDay(),
        ]);

        $order = Order::factory()->create();

        Livewire::test(AssignmentForm::class)
            ->set('form.order_id', $order->id)
            ->set('form.truck_id', $truck->id)
            ->set('form.driver_id', $driver->id)
            ->set('form.start_date', Carbon::now()->addDay()->format('Y-m-d\TH:i'))
            ->set('form.end_date', Carbon::now()->addDays(2)->format('Y-m-d\TH:i'))
            ->set('form.status', 'scheduled')
            ->set('form.description', 'Ruta de prueba')
            ->call('save')
            ->assertHasErrors(['form.driver_id']);
    }

    public function test_auto_assignment_allocates_resources(): void
    {
        $this->actingAsFleetManager();

        $truck = Truck::factory()->create([
            'status' => 'available',
            'last_maintenance_mileage' => 10000,
            'maintenance_mileage_threshold' => 20000,
            'mileage' => 15000,
        ]);

        $driver = $this->createDriver();
        $order = Order::factory()->create([
            'status' => 'pending',
        ]);

        $start = Carbon::now()->addDay();
        $end = Carbon::now()->addDays(2);

        Livewire::test(AssignmentForm::class)
            ->set('form.order_id', $order->id)
            ->set('form.start_date', $start->format('Y-m-d\TH:i'))
            ->set('form.end_date', $end->format('Y-m-d\TH:i'))
            ->set('form.status', 'scheduled')
            ->set('form.description', 'Ruta automática')
            ->set('mode', 'automatic')
            ->call('save')
            ->assertRedirect(route('fleet.assignments.index'));

        $this->assertDatabaseCount('assignments', 1);

        $assignment = Assignment::first();
        $this->assertEquals($order->id, $assignment->order_id);
        $this->assertNotNull($assignment->truck_id);
        $this->assertNotNull($assignment->driver_id);
    }
}
