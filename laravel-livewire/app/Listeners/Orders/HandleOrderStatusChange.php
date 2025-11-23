<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderStatusChanged;
use App\Models\Assignment;
use App\Notifications\OrderStatusChangedNotification;
use App\Services\Logistics\InventorySyncService;
use Illuminate\Support\Facades\Notification;

class HandleOrderStatusChange
{
    public function __construct(private InventorySyncService $inventorySyncService)
    {
    }

    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;

        $this->notifyStakeholders($order, $event->previousStatus);

        if ($order->status === 'delivered') {
            $this->generateInvoice($order);
            $this->inventorySyncService->release($order);
        }

        if ($order->status === 'cancelled') {
            $this->inventorySyncService->release($order);
        }
    }

    protected function notifyStakeholders($order, string $previousStatus): void
    {
        if ($order->client && $order->client->email) {
            Notification::route('mail', $order->client->email)
                ->notify(new OrderStatusChangedNotification($order, $previousStatus));
        }

        if ($order->assignments()->with('driver')->exists()) {
            $order->loadMissing('assignments.driver');

            $order->assignments->each(function (Assignment $assignment) use ($order, $previousStatus) {
                if ($assignment->driver && $assignment->driver->email) {
                    Notification::route('mail', $assignment->driver->email)
                        ->notify(new OrderStatusChangedNotification($order, $previousStatus));
                }
            });
        }
    }

    protected function generateInvoice($order): void
    {
        if ($order->invoices()->exists()) {
            return;
        }

        $estimation = $order->estimated_cost ?? 0;
        $taxRate = config('logistics.billing.tax_rate', 0.16);
        $subtotal = $estimation;
        $tax = round($subtotal * $taxRate, 2);
        $total = round($subtotal + $tax, 2);

        $order->invoices()->create([
            'client_id' => $order->client_id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'issue_date' => now(),
            'due_date' => now()->addDays(15),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'status' => 'issued',
            'notes' => __('Factura generada automáticamente al entregar el pedido.'),
        ]);
    }

    protected function generateInvoiceNumber(): string
    {
        $prefix = config('logistics.billing.prefix', 'INV');

        return sprintf('%s-%s', $prefix, strtoupper(str()->random(8)));
    }
}
