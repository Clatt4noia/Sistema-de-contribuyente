<?php

namespace App\Enums\Fleet;

enum AssignmentStatus: string
{
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => __('Programado'),
            self::InProgress => __('En ruta'),
            self::Completed => __('Completado'),
            self::Cancelled => __('Cancelado'),
        };
    }
}

