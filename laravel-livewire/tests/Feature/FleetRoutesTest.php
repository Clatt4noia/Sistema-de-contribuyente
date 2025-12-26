<?php

namespace Tests\Feature;

use App\Models\Truck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetRoutesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function trucks_create_route_is_accessible_for_authenticated_users(): void
    {
        $user = User::factory()->fleetManager()->create();

        $response = $this->actingAs($user)->get(route('fleet.trucks.create'));

        $response->assertOk();
        $response->assertSeeTextInOrder([
            'Registrar Camion',
            'Placa',
            'Marca',
        ]);
    }

    /** @test */
    public function trucks_create_route_is_accessible_even_if_the_user_is_not_verified(): void
    {
        $user = User::factory()->fleetManager()->unverified()->create();

        $response = $this->actingAs($user)->get(route('fleet.trucks.create'));

        $response->assertOk();
        $response->assertSeeText('Registrar Camion');
    }

    /** @test */
    public function trucks_edit_route_displays_the_form_for_existing_trucks(): void
    {
        $user = User::factory()->fleetManager()->create();

        $truck = Truck::create([
            'plate_number' => 'ABC-123',
            'brand' => 'Volvo',
            'model' => 'FH16',
            'year' => 2024,
            'type' => 'Tractocamion',
            'mileage' => 12000,
        ]);

        $response = $this->actingAs($user)->get(route('fleet.trucks.edit', $truck));

        $response->assertOk();
        $response->assertSeeText('Editar Camion');
        $response->assertSeeText('Placa');
        $response->assertSee('FH16');

    }
}
