<?php

namespace Tests\Feature\Dashboards;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_can_access_its_dashboard(): void
    {
        $matrix = [
            'admin' => 'dashboards.admin',
            'logisticsManager' => 'dashboards.logistics',
            'fleetManager' => 'dashboards.fleet',
            'financeManager' => 'dashboards.finance',
            'financeAnalyst' => 'dashboards.finance-analyst',
            'client' => 'dashboards.client',
        ];

        foreach ($matrix as $factoryState => $route) {
            $user = User::factory()->{$factoryState}()->create();

            $response = $this->actingAs($user)->get(route($route));

            $response->assertOk();
        }
    }

    public function test_roles_cannot_access_foreign_dashboards(): void
    {
        $scenarios = [
            'logisticsManager' => ['dashboards.finance', 'dashboards.finance-analyst', 'dashboards.client', 'dashboards.admin'],
            'fleetManager' => ['dashboards.finance', 'dashboards.finance-analyst', 'dashboards.client', 'dashboards.logistics', 'dashboards.admin'],
            'financeManager' => ['dashboards.logistics', 'dashboards.client', 'dashboards.admin'],
            'financeAnalyst' => ['dashboards.logistics', 'dashboards.fleet', 'dashboards.client', 'dashboards.admin'],
            'client' => ['dashboards.logistics', 'dashboards.fleet', 'dashboards.finance', 'dashboards.admin'],
        ];

        foreach ($scenarios as $factoryState => $routes) {
            $user = User::factory()->{$factoryState}()->create();

            foreach ($routes as $route) {
                $response = $this->actingAs($user)->get(route($route));

                $response->assertForbidden();
            }
        }
    }

    public function test_navigation_menu_is_filtered_by_role(): void
    {
        $logisticsUser = User::factory()->logisticsManager()->create();
        $logisticsResponse = $this->actingAs($logisticsUser)->get(route('dashboards.logistics'));
        $logisticsResponse->assertOk();
        $logisticsResponse->assertSee('Panel logístico');
        $logisticsResponse->assertDontSee('Panel financiero');
        $logisticsResponse->assertDontSee('Portal del cliente');

        $financeUser = User::factory()->financeManager()->create();
        $financeResponse = $this->actingAs($financeUser)->get(route('dashboards.finance'));
        $financeResponse->assertOk();
        $financeResponse->assertSee('Panel financiero');
        $financeResponse->assertDontSee('Panel logístico');
        $financeResponse->assertDontSee('Portal del cliente');

        $clientUser = User::factory()->client()->create();
        $clientResponse = $this->actingAs($clientUser)->get(route('dashboards.client'));
        $clientResponse->assertOk();
        $clientResponse->assertSee('Portal del cliente');
        $clientResponse->assertDontSee('Panel financiero');
        $clientResponse->assertDontSee('Panel logístico');
    }
}
