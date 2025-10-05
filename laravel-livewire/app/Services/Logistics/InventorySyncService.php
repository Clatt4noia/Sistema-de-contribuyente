<?php

namespace App\Services\Logistics;

use App\Models\InventoryReservation;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InventorySyncService
{
    public function reserve(Order $order, array $items): void
    {
        foreach ($items as $item) {
            $reservation = InventoryReservation::create([
                'order_id' => $order->id,
                'item_sku' => $item['sku'],
                'quantity' => $item['quantity'],
                'status' => 'pending',
                'source_system' => $item['source_system'] ?? 'internal',
                'payload' => $item,
            ]);

            if ($this->shouldNotifyExternal()) {
                $response = Http::withHeaders($this->headers())
                    ->post(config('logistics.inventory.endpoint').'/reserve', [
                        'sku' => $item['sku'],
                        'quantity' => $item['quantity'],
                        'order_reference' => $order->reference,
                    ]);

                if ($response->successful()) {
                    $reservation->update([
                        'status' => 'confirmed',
                        'reserved_at' => now(),
                    ]);
                } else {
                    $reservation->update(['status' => 'failed']);
                    Log::warning('Fallo al sincronizar reserva de inventario', [
                        'order_id' => $order->id,
                        'response' => $response->body(),
                    ]);
                }
            } else {
                $reservation->update([
                    'status' => 'confirmed',
                    'reserved_at' => now(),
                ]);
            }
        }
    }

    public function release(Order $order): void
    {
        $order->inventoryReservations()->where('status', 'confirmed')->each(function (InventoryReservation $reservation) {
            $reservation->update([
                'status' => 'released',
                'released_at' => now(),
            ]);

            if ($this->shouldNotifyExternal()) {
                Http::withHeaders($this->headers())
                    ->post(config('logistics.inventory.endpoint').'/release', [
                        'sku' => $reservation->item_sku,
                        'quantity' => $reservation->quantity,
                    ]);
            }
        });
    }

    protected function shouldNotifyExternal(): bool
    {
        return (bool) config('logistics.inventory.endpoint');
    }

    protected function headers(): array
    {
        return array_filter([
            'Authorization' => config('logistics.inventory.token')
                ? 'Bearer '.config('logistics.inventory.token')
                : null,
            'Accept' => 'application/json',
        ]);
    }
}
