<?php

namespace Tests\Unit\Services\Logistics;

use App\Models\Order;
use App\Models\RoutePlan;
use App\Services\Logistics\RouteOptimizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteOptimizationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_route_plan_with_sorted_stops_and_map_url(): void
    {
        config(['services.maps.google_api_key' => 'test-api-key']);

        $order = Order::factory()->create([
            'origin_latitude' => -12.05,
            'origin_longitude' => -77.05,
            'destination_latitude' => -11.95,
            'destination_longitude' => -76.95,
        ]);

        $stops = [
            ['name' => 'Cliente B', 'latitude' => -12.10, 'longitude' => -77.10],
            ['name' => 'Cliente A', 'latitude' => -12.02, 'longitude' => -77.02],
        ];

        $service = new RouteOptimizationService();
        $routePlan = $service->createOrUpdatePlan($order, $stops);

        $this->assertInstanceOf(RoutePlan::class, $routePlan);
        $this->assertCount(2, $routePlan->route_data['stops']);
        $this->assertSame('Cliente A', $routePlan->route_data['stops'][0]['name']);
        $this->assertStringContainsString('https://www.google.com/maps/embed/v1/directions', (string) $routePlan->map_url);
        $this->assertStringContainsString('key=test-api-key', (string) $routePlan->map_url);
        $this->assertArrayHasKey('distance_km', $routePlan->route_data);
        $this->assertArrayHasKey('duration_minutes', $routePlan->route_data);
    }
}
