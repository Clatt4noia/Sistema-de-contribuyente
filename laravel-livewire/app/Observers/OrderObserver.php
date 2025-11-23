<?php

namespace App\Observers;

use App\Events\Orders\OrderCostParametersChanged;
use App\Events\Orders\OrderCreated;
use App\Events\Orders\OrderScheduleChanged;
use App\Events\Orders\OrderStatusChanged;
use App\Models\Order;

class OrderObserver
{
    public function created(Order $order): void
    {
        OrderCreated::dispatch($order);
    }

    public function updated(Order $order): void
    {
        $previousStatus = (string) $order->getOriginal('status');

        if ($order->wasChanged('status')) {
            OrderStatusChanged::dispatch($order, $previousStatus);
        }

        if ($order->wasChanged(['pickup_date', 'delivery_date', 'origin_latitude', 'destination_latitude'])) {
            OrderScheduleChanged::dispatch($order);
        }

        if ($order->wasChanged(['cargo_weight_kg', 'cargo_volume_m3', 'estimated_distance_km'])) {
            OrderCostParametersChanged::dispatch($order);
        }
    }
}
