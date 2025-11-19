<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';



use App\Enums\UserRole;
use App\Livewire\Dashboards\AdminDashboard;
use App\Livewire\Dashboards\ClientDashboard;
use App\Livewire\Dashboards\FinanceAnalystDashboard;
use App\Livewire\Dashboards\FinanceDashboard;
use App\Livewire\ClientPortal\OrderTracker as ClientOrderTracker;
use App\Livewire\Dashboards\FleetDashboard;
use App\Livewire\Dashboards\LogisticsDashboard;
use App\Livewire\Fleet\AvailabilityBoard;
use App\Livewire\Fleet\AssignmentForm;
use App\Livewire\Fleet\AssignmentList;
use App\Livewire\Fleet\DriverForm;
use App\Livewire\Fleet\DriverList;
use App\Livewire\Fleet\MaintenanceForm;
use App\Livewire\Fleet\MaintenanceList;
use App\Livewire\Fleet\TruckList;
use App\Livewire\Fleet\Report as FleetReport;
use App\Livewire\Logistics\LiveTrackingBoard;
use App\Livewire\Orders\OrderForm;
use App\Livewire\Orders\OrderList;
use App\Livewire\Clients\ClientForm;
use App\Livewire\Clients\ClientList;
use App\Http\Controllers\Billing\InvoiceFileController;
use App\Livewire\Billing\CreateInvoice;
use App\Livewire\Billing\ElectronicInvoiceForm;
use App\Livewire\Billing\InvoiceForm;
use App\Livewire\Billing\InvoiceList;
use App\Livewire\Billing\PaymentForm;
use App\Livewire\Billing\PaymentList;
use App\Livewire\Billing\TransportGuides\TransportGuideForm;
use App\Livewire\Billing\TransportGuides\TransportGuideIndex;
use App\Livewire\Billing\TransportGuides\TransportGuideShow;
use App\Livewire\Finance\TransactionAnalytics;

use App\Livewire\Finance\TransactionList;
use App\Models\TransportGuide;
use App\Models\Driver;
use App\Models\Truck;
use App\Http\Controllers\Billing\TransportGuideFileController;

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        $user = auth()->user();

        $route = match ($user->role) {
            UserRole::ADMIN => 'dashboards.admin',
            UserRole::LOGISTICS_MANAGER => 'dashboards.logistics',
            UserRole::FLEET_MANAGER => 'dashboards.fleet',
            UserRole::FINANCE_MANAGER => 'dashboards.finance',
            UserRole::FINANCE_ANALYST => 'dashboards.finance-analyst',
            UserRole::CLIENT => 'dashboards.client',
            default => 'dashboards.client',
        };

        return redirect()->route($route);
    })->name('dashboard');

    Route::redirect('/home', '/')->name('home');

    Route::prefix('dashboards')->name('dashboards.')->group(function () {
        Route::get('/admin', AdminDashboard::class)
            ->middleware('can:view-dashboard.admin')
            ->name('admin');

        Route::get('/logistics', LogisticsDashboard::class)
            ->middleware('can:view-dashboard.logistics')
            ->name('logistics');

        Route::get('/logistics/tracking', LiveTrackingBoard::class)
            ->middleware('can:view-dashboard.logistics')
            ->name('logistics-tracking');

        Route::get('/fleet', FleetDashboard::class)
            ->middleware('can:view-dashboard.fleet')
            ->name('fleet');

        Route::get('/finance', FinanceDashboard::class)
            ->middleware('can:view-dashboard.finance')
            ->name('finance');

        Route::get('/finance-analyst', FinanceAnalystDashboard::class)
            ->middleware('can:view-dashboard.finance-analyst')
            ->name('finance-analyst');

        Route::get('/client', ClientDashboard::class)
            ->middleware('can:view-dashboard.client')
            ->name('client');
    });

    Route::get('/drivers', fn () => redirect()->route('fleet.drivers.index'))
        ->name('legacy.drivers.index');
    Route::get('/drivers/create', function () {
        return view('pages.fleet.drivers.create');
    })->name('legacy.drivers.create');
    Route::get('/drivers/{driver}/edit', function (Driver $driver) {
        return view('pages.fleet.drivers.edit', ['driver' => $driver]);
    })->whereNumber('driver')->name('legacy.drivers.edit');

    Route::prefix('fleet')->name('fleet.')->group(function () {
        Route::get('/trucks', TruckList::class)->name('trucks.index');
        Route::view('/trucks/create', 'pages.fleet.trucks.create')->name('trucks.create');
        Route::get('/trucks/{truck}/edit', function (Truck $truck) {
            return view('pages.fleet.trucks.edit', ['truck' => $truck]);
        })->whereNumber('truck')->name('trucks.edit');

        Route::get('/drivers', DriverList::class)->name('drivers.index');
        Route::get('/drivers/create', function () {
            return view('pages.fleet.drivers.create');
        })->name('drivers.create');
        Route::get('/drivers/{driver}/edit', function (Driver $driver) {
            return view('pages.fleet.drivers.edit', ['driver' => $driver]);
        })->whereNumber('driver')->name('drivers.edit');

        Route::get('/maintenance', MaintenanceList::class)->name('maintenance.index');
        Route::get('/maintenance/create', MaintenanceForm::class)->name('maintenance.create');
        Route::get('/maintenance/{id}/edit', MaintenanceForm::class)->whereNumber('id')->name('maintenance.edit');

        Route::get('/assignments', AssignmentList::class)->name('assignments.index');
        Route::get('/assignments/create', AssignmentForm::class)->name('assignments.create');
        Route::get('/assignments/{id}/edit', AssignmentForm::class)->whereNumber('id')->name('assignments.edit');
        Route::get('/availability', AvailabilityBoard::class)->name('availability');
        Route::get('/report', FleetReport::class)->name('report');
    });

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', OrderList::class)->name('index');
        Route::get('/create', OrderForm::class)->name('create');
        Route::get('/{order}/edit', OrderForm::class)->name('edit');
    });

    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', ClientList::class)->name('index');
        Route::get('/create', ClientForm::class)->name('create');
        Route::get('/{client}/edit', ClientForm::class)->name('edit');
    });

    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/invoices', InvoiceList::class)->name('invoices.index');
        Route::get('/invoices/create', CreateInvoice::class)->name('invoices.create');
        Route::get('/invoices/{invoice}/edit', InvoiceForm::class)->whereNumber('invoice')->name('invoices.edit');
        Route::get('/invoices/{invoice}/electronic', ElectronicInvoiceForm::class)->whereNumber('invoice')->name('invoices.electronic');

        Route::get('/payments', PaymentList::class)->name('payments.index');
        Route::get('/payments/create', PaymentForm::class)->name('payments.create');
        Route::get('/payments/{payment}/edit', PaymentForm::class)->whereNumber('payment')->name('payments.edit');

        Route::get('/transport-guides', TransportGuideIndex::class)
            ->name('transport-guides.index')
            ->can('viewAny', TransportGuide::class);

        Route::get('/transport-guides/create', TransportGuideForm::class)
            ->name('transport-guides.create')
            ->can('create', TransportGuide::class);

        Route::get('/transport-guides/{transportGuide}', TransportGuideShow::class)
            ->name('transport-guides.show')
            ->can('view', 'transportGuide');

        Route::get('/transport-guides/{transportGuide}/edit', TransportGuideForm::class)
            ->name('transport-guides.edit')
            ->can('update', 'transportGuide');

        Route::get('/transport-guides/{transportGuide}/xml', [TransportGuideFileController::class, 'xml'])
            ->name('transport-guides.xml')
            ->can('view', 'transportGuide');

        Route::get('/transport-guides/{transportGuide}/cdr', [TransportGuideFileController::class, 'cdr'])
            ->name('transport-guides.cdr')
            ->can('view', 'transportGuide');

        Route::get('/transport-guides/{transportGuide}/pdf', [TransportGuideFileController::class, 'pdf'])
            ->name('transport-guides.pdf')
            ->can('view', 'transportGuide');
    });

    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/transactions', TransactionList::class)
            ->name('transactions.index');
        Route::get('/transactions/analytics', TransactionAnalytics::class)
            ->name('transactions.analytics');

    });

    Route::middleware('signed')->group(function () {
        Route::get('/billing/invoices/{invoice}/download/xml', [InvoiceFileController::class, 'xml'])->name('billing.invoices.download.xml');
        Route::get('/billing/invoices/{invoice}/download/cdr', [InvoiceFileController::class, 'cdr'])->name('billing.invoices.download.cdr');
        Route::get('/billing/invoices/{invoice}/download/pdf', [InvoiceFileController::class, 'pdf'])->name('billing.invoices.download.pdf');

        Route::get('/billing/transport-guides/{transportGuide}/download/xml', [TransportGuideFileController::class, 'xml'])->name('billing.transport-guides.download.xml');
        Route::get('/billing/transport-guides/{transportGuide}/download/cdr', [TransportGuideFileController::class, 'cdr'])->name('billing.transport-guides.download.cdr');
        Route::get('/billing/transport-guides/{transportGuide}/download/pdf', [TransportGuideFileController::class, 'pdf'])->name('billing.transport-guides.download.pdf');
    });

    Route::prefix('portal')->name('portal.')->middleware('can:view-dashboard.client')->group(function () {
        Route::get('/orders', ClientOrderTracker::class)->name('orders');
    });

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
});
