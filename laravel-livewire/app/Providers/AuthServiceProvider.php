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
use App\Models\Transaction;
use App\Models\Truck;
use App\Models\TransportGuide;
use App\Models\User;
use App\Policies\AssignmentPolicy;
use App\Policies\AssignmentPolicyV2;
use App\Policies\ClientPolicy;
use App\Policies\ClientPolicyV2;
use App\Policies\DriverPolicy;
use App\Policies\DriverPolicyV2;
use App\Policies\InvoicePolicy;
use App\Policies\InvoicePolicyV2;
use App\Policies\MaintenancePolicy;
use App\Policies\MaintenancePolicyV2;
use App\Policies\OrderPolicy;
use App\Policies\OrderPolicyV2;
use App\Policies\PaymentPolicy;
use App\Policies\PaymentPolicyV2;
use App\Policies\TransactionPolicy;
use App\Policies\TruckPolicy;
use App\Policies\TruckPolicyV2;
use App\Policies\TransportGuidePolicy;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Durante la transición evitamos eliminar los mapeos existentes. La nueva
     * versión basada en enums queda registrada primero para que Gate utilice
     * TruckPolicyV2, DriverPolicyV2, etc., pero mantenemos las clases previas
     * en caso otros equipos aún dependan de ellas.
     */
    protected $policies = [
        Truck::class => TruckPolicyV2::class,
        Driver::class => DriverPolicyV2::class,
        Maintenance::class => MaintenancePolicyV2::class,
        Assignment::class => AssignmentPolicyV2::class,
        Order::class => OrderPolicyV2::class,
        Client::class => ClientPolicyV2::class,
        Invoice::class => InvoicePolicyV2::class,
        Payment::class => PaymentPolicyV2::class,
        Transaction::class => TransactionPolicy::class,
        TransportGuide::class => TransportGuidePolicy::class,

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
