<?php

namespace App\Enums\Orders;

enum OrderStatus: string
{
    case Pending = 'pending';
    case EnRoute = 'en_route';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pendiente'),
            self::EnRoute => __('En ruta'),
            self::Delivered => __('Entregado'),
            self::Cancelled => __('Cancelado'),
        };
    }
}

