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

        $response = $this->actingAs($user)->get('/fleet/trucks/create');

        dd($response->getStatusCode(), $response->getContent());
    }
}
