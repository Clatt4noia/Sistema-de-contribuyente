<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderCreated;
use App\Models\Assignment;
use App\Notifications\OrderAssignedNotification;
use App\Services\Logistics\OrderAssignmentService;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Notification;

class AssignResourcesToOrder
{
    public function __construct(private OrderAssignmentService $assignmentService)
    {
    }

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        if ($order->assignments()->exists()) {
            return;
        }

        $assignment = $this->assignmentService->assignBestResources($order);

        if (! $assignment instanceof Assignment) {
            return;
        }

        $this->notifyAssignment($assignment, $order);
    }

    public function subscribe(Dispatcher $events): void
    {
        // No-op method to opt-in for subscription discovery if desired.
    }

    protected function notifyAssignment(Assignment $assignment, $order): void
    {
        if ($assignment->driver && $assignment->driver->email) {
            Notification::route('mail', $assignment->driver->email)
                ->notify(new OrderAssignedNotification($assignment));
        }

        if ($order->client && $order->client->email) {
            Notification::route('mail', $order->client->email)
                ->notify(new OrderAssignedNotification($assignment));
        }
    }
}
