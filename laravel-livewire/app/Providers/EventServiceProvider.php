<?php

namespace App\Providers;

use App\Events\Orders\OrderCostParametersChanged;
use App\Events\Orders\OrderCreated;
use App\Events\Orders\OrderScheduleChanged;
use App\Events\Orders\OrderStatusChanged;
use App\Listeners\Orders\AssignResourcesToOrder;
use App\Listeners\Orders\EstimateOrderCosts;
use App\Listeners\Orders\HandleOrderStatusChange;
use App\Listeners\Orders\RecalculateOrderCostEstimation;
use App\Listeners\Orders\ReserveOrderInventory;
use App\Listeners\Orders\UpdateOrderRoutePlan;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OrderCreated::class => [
            AssignResourcesToOrder::class,
            ReserveOrderInventory::class,
            EstimateOrderCosts::class,
        ],
        OrderStatusChanged::class => [
            HandleOrderStatusChange::class,
        ],
        OrderScheduleChanged::class => [
            UpdateOrderRoutePlan::class,
        ],
        OrderCostParametersChanged::class => [
            RecalculateOrderCostEstimation::class,
        ],
    ];
}
