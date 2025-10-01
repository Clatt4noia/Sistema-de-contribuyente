<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Maintenance;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Truck;
use App\Models\User;
use App\Policies\AssignmentPolicy;
use App\Policies\ClientPolicy;
use App\Policies\DriverPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\MaintenancePolicy;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\TruckPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Truck::class => TruckPolicy::class,
        Driver::class => DriverPolicy::class,
        Maintenance::class => MaintenancePolicy::class,
        Assignment::class => AssignmentPolicy::class,
        Order::class => OrderPolicy::class,
        Client::class => ClientPolicy::class,
        Invoice::class => InvoicePolicy::class,
        Payment::class => PaymentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }

            return null;
        });

        Gate::define('view-dashboard.admin', fn (User $user) => $user->hasRole(UserRole::ADMIN));

        Gate::define('view-dashboard.logistics', fn (User $user) => $user->hasAnyRole([
            UserRole::LOGISTICS_MANAGER,
        ]));

        Gate::define('view-dashboard.fleet', fn (User $user) => $user->hasAnyRole([
            UserRole::FLEET_MANAGER,
            UserRole::LOGISTICS_MANAGER,
        ]));

        Gate::define('view-dashboard.finance', fn (User $user) => $user->hasAnyRole([
            UserRole::FINANCE_MANAGER,
        ]));

        Gate::define('view-dashboard.finance-analyst', fn (User $user) => $user->hasAnyRole([
            UserRole::FINANCE_ANALYST,
            UserRole::FINANCE_MANAGER,
        ]));

        Gate::define('view-dashboard.client', fn (User $user) => $user->hasRole(UserRole::CLIENT));
    }
}
