<?php

namespace App\Providers;

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
    }
}
