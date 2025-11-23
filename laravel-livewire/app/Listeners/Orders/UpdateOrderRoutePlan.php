<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderScheduleChanged;
use App\Services\Logistics\RouteOptimizationService;

class UpdateOrderRoutePlan
{
    public function __construct(private RouteOptimizationService $routeOptimizationService)
    {
    }

    public function handle(OrderScheduleChanged $event): void
    {
        $this->routeOptimizationService->createOrUpdatePlan($event->order);
    }
}
