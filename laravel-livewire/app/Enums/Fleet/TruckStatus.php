<?php

namespace App\Enums\Fleet;

enum TruckStatus: string
{
    case Available = 'available';
    case InUse = 'in_use';
    case Maintenance = 'maintenance';
    case OutOfService = 'out_of_service';
    case Reserved = 'reserved';

    public function label(): string
    {
        return match ($this) {
            self::Available => __('Disponible'),
            self::InUse => __('En uso'),
            self::Maintenance => __('Mantenimiento'),
            self::OutOfService => __('Fuera de servicio'),
            self::Reserved => __('Reservado'),
        };
    }
}

