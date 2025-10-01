<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case LOGISTICS_MANAGER = 'logistics_manager';
    case FLEET_MANAGER = 'fleet_manager';
    case FINANCE_MANAGER = 'finance_manager';
    case FINANCE_ANALYST = 'finance_analyst';
    case CLIENT = 'client';

    /**
     * Obtener una etiqueta legible para mostrar en formularios/tablas.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => __('Administrador (acceso completo)'),
            self::LOGISTICS_MANAGER => __('Gestor logístico (operaciones y rutas)'),
            self::FLEET_MANAGER => __('Jefe de flota (mantenimiento y disponibilidad)'),
            self::FINANCE_MANAGER => __('Responsable financiero (facturación y cobros)'),
            self::FINANCE_ANALYST => __('Analista financiero (conciliación y reportes)'),
            self::CLIENT => __('Cliente (portal externo)'),
        };
    }

    /**
     * Roles habilitados para auto-registro desde el onboarding interno.
     *
     * @return array<int, self>
     */
    public static function forSelfRegistration(): array
    {
        return [
            self::ADMIN,
            self::LOGISTICS_MANAGER,
            self::FINANCE_MANAGER,
        ];
    }

    /**
     * Roles con acceso completo a la capa logística (flota + órdenes).
     *
     * @return array<int, self>
     */
    public static function forLogistics(): array
    {
        return [
            self::ADMIN,
            self::LOGISTICS_MANAGER,
            self::FLEET_MANAGER,
        ];
    }

    /**
     * Roles con acceso financiero.
     *
     * @return array<int, self>
     */
    public static function forFinance(): array
    {
        return [
            self::ADMIN,
            self::FINANCE_MANAGER,
            self::FINANCE_ANALYST,
        ];
    }

    /**
     * Roles que corresponden al portal de clientes.
     *
     * @return array<int, self>
     */
    public static function forClientPortal(): array
    {
        return [
            self::CLIENT,
        ];
    }
}
