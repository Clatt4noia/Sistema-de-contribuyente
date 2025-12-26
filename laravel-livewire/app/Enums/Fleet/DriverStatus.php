<?php

namespace App\Enums\Fleet;

enum DriverStatus: string
{
    case Active = 'active';
    case Assigned = 'assigned';
    case Inactive = 'inactive';
    case OnLeave = 'on_leave';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Activo'),
            self::Assigned => __('Asignado'),
            self::Inactive => __('Inactivo'),
            self::OnLeave => __('Permiso'),
        };
    }
}

