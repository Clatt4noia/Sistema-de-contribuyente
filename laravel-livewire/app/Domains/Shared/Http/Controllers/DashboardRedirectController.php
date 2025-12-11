<?php

namespace App\Domains\Shared\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;

class DashboardRedirectController
{
    public function __invoke(): RedirectResponse
    {
        $user = auth()->user();

        $route = match ($user?->role) {
            UserRole::ADMIN => 'dashboards.admin',
            UserRole::LOGISTICS_MANAGER => 'dashboards.logistics',
            UserRole::FLEET_MANAGER => 'dashboards.fleet',
            UserRole::FINANCE_MANAGER => 'dashboards.finance',
            UserRole::FINANCE_ANALYST => 'dashboards.finance-analyst',
            UserRole::CLIENT => 'dashboards.client',
            default => 'dashboards.client',
        };

        return redirect()->route($route);
    }
}
