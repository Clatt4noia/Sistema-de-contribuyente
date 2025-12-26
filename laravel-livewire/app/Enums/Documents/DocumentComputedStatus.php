<?php

namespace App\Enums\Documents;

enum DocumentComputedStatus: string
{
    case VALID = 'VALID';
    case EXPIRING = 'EXPIRING';
    case EXPIRED = 'EXPIRED';

    public function label(): string
    {
        return match ($this) {
            self::VALID => __('VIGENTE'),
            self::EXPIRING => __('POR VENCER'),
            self::EXPIRED => __('VENCIDO'),
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::VALID => 'bg-success-soft text-success-strong ',
            self::EXPIRING => 'bg-warning-soft text-warning ',
            self::EXPIRED => 'bg-danger-soft text-danger-strong ',
        };
    }
}

