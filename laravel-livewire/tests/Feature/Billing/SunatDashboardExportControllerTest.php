<?php

namespace Tests\Feature\Billing;

use App\Models\User;
use App\Support\Billing\SunatStatusAggregator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class SunatDashboardExportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsFinanceManager(): User
    {
        $user = User::factory()->financeManager()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_filters_are_validated_before_export(): void
    {
        $this->actingAsFinanceManager();

        $aggregator = Mockery::mock(SunatStatusAggregator::class);
        $aggregator->shouldNotReceive('forFilters');
        $this->app->instance(SunatStatusAggregator::class, $aggregator);

        $response = $this->get(route('billing.sunat-dashboard.export.excel', [
            'date_from' => 'not-a-date',
        ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['date_from']);
    }

    public function test_valid_filters_are_passed_to_aggregator(): void
    {
        $this->actingAsFinanceManager();

        $filters = [
            'date_from' => '2024-01-01',
            'date_to' => '2024-01-31',
            'series' => 'F001',
            'document_type' => 'invoice',
            'sunat_status' => 'all',
        ];

        $aggregator = Mockery::mock(SunatStatusAggregator::class);
        $aggregator->shouldReceive('forFilters')
            ->once()
            ->with(array_merge($filters, ['sunat_status' => '']))
            ->andReturn(Collection::make());

        $this->app->instance(SunatStatusAggregator::class, $aggregator);

        $response = $this->get(route('billing.sunat-dashboard.export.excel', $filters));

        $response->assertOk();
    }
}
