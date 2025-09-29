<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetRoutesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function trucks_create_route_is_accessible_for_authenticated_users(): void
    {
        $user = User::factory()->create();

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
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('fleet.trucks.create'));

        $response->assertOk();
        $response->assertSeeText('Registrar Camion');

    }
}
