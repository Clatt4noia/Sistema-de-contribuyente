<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderCreated;
use App\Services\Logistics\CostEstimator;

class EstimateOrderCosts
{
    public function __construct(private CostEstimator $costEstimator)
    {
    }

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        $estimation = $this->costEstimator->estimate($order);

        $order->forceFill([
            'estimated_cost' => $estimation['total'],
            'cost_breakdown' => $estimation['breakdown'],
        ])->saveQuietly();
    }
}
