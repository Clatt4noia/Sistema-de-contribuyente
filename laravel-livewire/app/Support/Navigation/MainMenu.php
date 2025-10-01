<?php

namespace App\Support\Navigation;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Arr;

class MainMenu
{
    /**
     * Build the navigation menu for the authenticated user.
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
     * Base menu items definition.
     *
     * @return array<int, array<string, mixed>>
     */
    protected static function items(): array
    {
        return [
            [
                'label' => __('Panel administrativo'),
                'icon' => 'shield-check',
                'route' => 'dashboards.admin',
                'active' => ['dashboards.admin'],
                'roles' => [User::ROLE_ADMIN],
            ],
            [
                'label' => __('Panel logístico'),
                'icon' => 'compass',
                'route' => 'dashboards.logistics',
                'active' => ['dashboards.logistics'],
                'roles' => [User::ROLE_LOGISTICS_MANAGER, User::ROLE_ADMIN],
            ],
            [
                'label' => __('Panel de flota'),
                'icon' => 'truck',
                'route' => 'dashboards.fleet',
                'active' => ['dashboards.fleet'],
                'roles' => [User::ROLE_FLEET_MANAGER, User::ROLE_LOGISTICS_MANAGER, User::ROLE_ADMIN],
            ],
            [
                'label' => __('Panel financiero'),
                'icon' => 'banknote',
                'route' => 'dashboards.finance',
                'active' => ['dashboards.finance'],
                'roles' => [User::ROLE_FINANCE_MANAGER, User::ROLE_ADMIN],
            ],
            [
                'label' => __('Panel analista'),
                'icon' => 'line-chart',
                'route' => 'dashboards.finance-analyst',
                'active' => ['dashboards.finance-analyst'],
                'roles' => [User::ROLE_FINANCE_ANALYST, User::ROLE_FINANCE_MANAGER, User::ROLE_ADMIN],
            ],
            [
                'label' => __('Portal del cliente'),
                'icon' => 'user-circle',
                'route' => 'dashboards.client',
                'active' => ['dashboards.client'],
                'roles' => [User::ROLE_CLIENT],
            ],
            [
                'label' => __('Analíticas de flota'),
                'icon' => 'bar-chart-3',
                'route' => 'fleet.report',
                'active' => ['fleet.report'],
                'roles' => [User::ROLE_LOGISTICS_MANAGER, User::ROLE_FLEET_MANAGER, User::ROLE_ADMIN],
            ],
            [
                'label' => __('Camiones'),
                'icon' => 'truck',
                'route' => 'fleet.trucks.index',
                'active' => ['fleet.trucks.*'],
                'roles' => [User::ROLE_LOGISTICS_MANAGER, User::ROLE_FLEET_MANAGER, User::ROLE_ADMIN],
            ],
            [
                'label' => __('Conductores'),
                'icon' => 'id-card',
                'route' => 'fleet.drivers.index',
                'active' => ['fleet.drivers.*'],
                'roles' => [User::ROLE_LOGISTICS_MANAGER, User::ROLE_FLEET_MANAGER, User::ROLE_ADMIN],
            ],
            [
                'label' => __('Mantenimientos'),
                'icon' => 'wrench',
                'route' => 'fleet.maintenance.index',
                'active' => ['fleet.maintenance.*'],
                'roles' => [User::ROLE_LOGISTICS_MANAGER, User::ROLE_FLEET_MANAGER, User::ROLE_ADMIN],
            ],
            [
                'label' => __('Asignaciones'),
                'icon' => 'map',
                'route' => 'fleet.assignments.index',
                'active' => ['fleet.assignments.*'],
                'roles' => [User::ROLE_LOGISTICS_MANAGER, User::ROLE_FLEET_MANAGER, User::ROLE_ADMIN],
            ],
            [
                'label' => __('Órdenes'),
                'icon' => 'package-search',
                'route' => 'orders.index',
                'active' => ['orders.*'],
                'roles' => [User::ROLE_LOGISTICS_MANAGER, User::ROLE_ADMIN],
            ],
            [
                'label' => __('Clientes'),
                'icon' => 'users',
                'route' => 'clients.index',
                'active' => ['clients.*'],
                'roles' => [User::ROLE_LOGISTICS_MANAGER, User::ROLE_ADMIN],
            ],
            [
                'label' => __('Facturas'),
                'icon' => 'receipt',
                'route' => 'billing.invoices.index',
                'active' => ['billing.invoices.*'],
                'roles' => [User::ROLE_FINANCE_MANAGER, User::ROLE_FINANCE_ANALYST, User::ROLE_ADMIN],
            ],
            [
                'label' => __('Pagos'),
                'icon' => 'credit-card',
                'route' => 'billing.payments.index',
                'active' => ['billing.payments.*'],
                'roles' => [User::ROLE_FINANCE_MANAGER, User::ROLE_FINANCE_ANALYST, User::ROLE_ADMIN],
            ],
            [
                'label' => __('Mi perfil'),
                'icon' => 'settings',
                'route' => 'profile.edit',
                'active' => ['profile.*', 'password.*', 'appearance.*'],
                'roles' => null,
            ],
        ];
    }
}
