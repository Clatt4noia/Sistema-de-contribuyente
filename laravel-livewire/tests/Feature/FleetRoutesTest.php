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
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get(route('fleet.trucks.create'));

        $response->assertOk();
        $response->assertSeeTextInOrder([
            'Registrar Camion',
            'Placa',
            'Marca',
        ]);
    }

    /** @test */
    public function unverified_users_are_redirected_to_email_verification_notice(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)->get(route('fleet.trucks.create'));

        $response->assertRedirect(route('verification.notice'));
    }
}
