<?php

namespace App\Support\Navigation;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Arr;

/**
 * MainMenuV2 utiliza App\Enums\UserRole para evitar duplicar strings de roles
 * y sirve como reemplazo gradual de MainMenu.
 */
class MainMenuV2
{
    /**
     * Construye el menú principal respetando la nueva matriz de roles.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function for(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return collect(self::items())
            ->filter(function (array $item) use ($user) {
                $roles = Arr::get($item, 'roles');

                if ($roles === null) {
                    return true;
                }

                return $user->hasAnyRole($roles);
            })
            ->map(function (array $item) {
                $routeName = Arr::get($item, 'route');
                $active = Arr::get($item, 'active', $routeName ? [$routeName] : []);

                return array_merge($item, [
                    'href' => $routeName ? route($routeName) : '#',
                    'current' => $active ? request()->routeIs(...Arr::wrap($active)) : false,
                ]);
            })
            ->values()
            ->all();
    }

    /**
     * Definición base de ítems usando enums para cada rol.
     *
     * @return array<int, array<string, mixed>>
     */
    protected static function items(): array
    {
        return [
            [
                'label' => __('Panel administrativo'),
                'icon' => 'heroicon-o-shield-check',
                'route' => 'dashboards.admin',
                'active' => ['dashboards.admin'],
                'roles' => [UserRole::ADMIN],
            ],
            [
                'label' => __('Panel logístico'),
                'icon' => 'heroicon-o-compass',
                'route' => 'dashboards.logistics',
                'active' => ['dashboards.logistics'],
                'roles' => [UserRole::LOGISTICS_MANAGER, UserRole::ADMIN],
            ],
            [
                'label' => __('Panel de flota'),
                'icon' => 'heroicon-o-truck',
                'route' => 'dashboards.fleet',
                'active' => ['dashboards.fleet'],
                'roles' => [UserRole::FLEET_MANAGER, UserRole::LOGISTICS_MANAGER, UserRole::ADMIN],
            ],
            [
                'label' => __('Panel financiero'),
                'icon' => 'heroicon-o-banknotes',
                'route' => 'dashboards.finance',
                'active' => ['dashboards.finance'],
                'roles' => [UserRole::FINANCE_MANAGER, UserRole::ADMIN],
            ],
            [
                'label' => __('Panel analista'),
                'icon' => 'heroicon-o-presentation-chart-line',
                'route' => 'dashboards.finance-analyst',
                'active' => ['dashboards.finance-analyst'],
                'roles' => [UserRole::FINANCE_ANALYST, UserRole::FINANCE_MANAGER, UserRole::ADMIN],
            ],
            [
                'label' => __('Portal del cliente'),
                'icon' => 'heroicon-o-user-circle',
                'route' => 'dashboards.client',
                'active' => ['dashboards.client'],
                'roles' => [UserRole::CLIENT],
            ],
            [
                'label' => __('Analíticas de flota'),
                'icon' => 'heroicon-o-presentation-chart-bar',
                'route' => 'fleet.report',
                'active' => ['fleet.report'],
                'roles' => [UserRole::LOGISTICS_MANAGER, UserRole::FLEET_MANAGER, UserRole::ADMIN],
            ],
            [
                'label' => __('Camiones'),
                'icon' => 'heroicon-o-truck',
                'route' => 'fleet.trucks.index',
                'active' => ['fleet.trucks.*'],
                'roles' => [UserRole::LOGISTICS_MANAGER, UserRole::FLEET_MANAGER, UserRole::ADMIN],
            ],
            [
                'label' => __('Conductores'),
                'icon' => 'heroicon-o-identification',
                'route' => 'fleet.drivers.index',
                'active' => ['fleet.drivers.*'],
                'roles' => [UserRole::LOGISTICS_MANAGER, UserRole::FLEET_MANAGER, UserRole::ADMIN],
            ],
            [
                'label' => __('Mantenimientos'),
                'icon' => 'heroicon-o-wrench-screwdriver',
                'route' => 'fleet.maintenance.index',
                'active' => ['fleet.maintenance.*'],
                'roles' => [UserRole::LOGISTICS_MANAGER, UserRole::FLEET_MANAGER, UserRole::ADMIN],
            ],
            [
                'label' => __('Asignaciones'),
                'icon' => 'heroicon-o-map',
                'route' => 'fleet.assignments.index',
                'active' => ['fleet.assignments.*'],
                'roles' => [UserRole::LOGISTICS_MANAGER, UserRole::FLEET_MANAGER, UserRole::ADMIN],
            ],
            [
                'label' => __('Órdenes'),
                'icon' => 'heroicon-o-clipboard-document-list',
                'route' => 'orders.index',
                'active' => ['orders.*'],
                'roles' => [UserRole::LOGISTICS_MANAGER, UserRole::ADMIN],
            ],
            [
                'label' => __('Clientes'),
                'icon' => 'heroicon-o-users',
                'route' => 'clients.index',
                'active' => ['clients.*'],
                'roles' => [UserRole::LOGISTICS_MANAGER, UserRole::ADMIN],
            ],
            [
                'label' => __('Facturas'),
                'icon' => 'heroicon-o-document-text',
                'route' => 'billing.invoices.index',
                'active' => ['billing.invoices.*'],
                'roles' => [UserRole::FINANCE_MANAGER, UserRole::FINANCE_ANALYST, UserRole::ADMIN],
            ],
            [
                'label' => __('Pagos'),
                'icon' => 'heroicon-o-credit-card',
                'route' => 'billing.payments.index',
                'active' => ['billing.payments.*'],
                'roles' => [UserRole::FINANCE_MANAGER, UserRole::FINANCE_ANALYST, UserRole::ADMIN],
            ],
            [
                'label' => __('Mi perfil'),
                'icon' => 'heroicon-o-cog-6-tooth',
                'route' => 'profile.edit',
                'active' => ['profile.*', 'password.*', 'appearance.*'],
                'roles' => null,
            ],
        ];
    }
}
