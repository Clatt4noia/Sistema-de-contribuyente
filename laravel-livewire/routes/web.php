<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

use App\Domains\Billing\Http\Controllers\InvoiceFileController;
use App\Domains\Billing\Http\Controllers\SunatDashboardExportController;
use App\Domains\Billing\Http\Controllers\TransportGuideCreateController;
use App\Domains\Billing\Http\Controllers\TransportGuideFileController;
use App\Domains\Dashboards\Livewire\AdminDashboard;
use App\Domains\Dashboards\Livewire\ClientDashboard;
use App\Domains\Dashboards\Livewire\FinanceAnalystDashboard;
use App\Domains\Dashboards\Livewire\FinanceDashboard;
use App\Domains\Dashboards\Livewire\FleetDashboard;
use App\Domains\Dashboards\Livewire\LogisticsDashboard;
use App\Domains\Billing\Livewire\CreateInvoice;
use App\Domains\Billing\Livewire\ElectronicInvoiceForm;
use App\Domains\Billing\Livewire\InvoiceForm;
use App\Domains\Billing\Livewire\InvoiceList;
use App\Domains\Billing\Livewire\PaymentForm;
use App\Domains\Billing\Livewire\PaymentList;
use App\Domains\Billing\Livewire\SunatUnifiedDashboard;
use App\Domains\Billing\Livewire\TransportGuides\TransportGuideForm;
use App\Domains\Billing\Livewire\TransportGuides\TransportGuideIndex;
use App\Domains\Billing\Livewire\TransportGuides\TransportGuideShow;
use App\Domains\ClientPortal\Livewire\OrderTracker as ClientOrderTracker;
use App\Domains\Clients\Livewire\ClientForm;
use App\Domains\Clients\Livewire\ClientList;
use App\Domains\Fleet\Http\Controllers\LegacyDriverController;
use App\Domains\Fleet\Livewire\AvailabilityBoard;
use App\Domains\Fleet\Livewire\AssignmentForm;
use App\Domains\Fleet\Livewire\AssignmentList;
use App\Domains\Fleet\Livewire\DriverList;
use App\Domains\Fleet\Livewire\MaintenanceForm;
use App\Domains\Fleet\Livewire\MaintenanceList;
use App\Domains\Fleet\Livewire\Report as FleetReport;
use App\Domains\Fleet\Livewire\TruckList;
use App\Domains\Finance\Livewire\CollectionsAndExpensesReport;
use App\Domains\Finance\Livewire\TransactionAnalytics;
use App\Domains\Finance\Livewire\TransactionList;
use App\Domains\Logistics\Livewire\LiveTrackingBoard;
use App\Domains\Logistics\Livewire\OrderStatusNotifications;
use App\Domains\Orders\Livewire\OrderForm;
use App\Domains\Orders\Livewire\OrderList;
use App\Domains\Shared\Http\Controllers\DashboardRedirectController;
use App\Models\Driver;
use App\Models\Transaction;
use App\Models\TransportGuide;
use App\Models\Truck;

// Alias claros para parámetros numéricos y evitar conflictos con rutas estáticas.
Route::pattern('transportGuide', '[0-9]+');
Route::pattern('driver', '[0-9]+');
Route::pattern('truck', '[0-9]+');

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardRedirectController::class)->name('dashboard');
    Route::redirect('/home', '/')->name('home');

    Route::prefix('dashboards')->name('dashboards.')->group(function () {
        Route::get('/admin', AdminDashboard::class)
            ->middleware('can:view-dashboard.admin')
            ->name('admin');

        Route::middleware('can:view-dashboard.logistics')->group(function () {
            Route::get('/logistics', LogisticsDashboard::class)->name('logistics');
            Route::get('/logistics/tracking', LiveTrackingBoard::class)->name('logistics-tracking');
            Route::get('/logistics/orders/notifications', OrderStatusNotifications::class)->name('logistics-notifications');
        });

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

    Route::prefix('drivers')->name('legacy.drivers.')->controller(LegacyDriverController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/{driver}/edit', 'edit')->name('edit');
    });

    Route::prefix('fleet')->name('fleet.')->group(function () {
        Route::get('/trucks', TruckList::class)->name('trucks.index');
        Route::view('/trucks/create', 'pages.fleet.trucks.create')->name('trucks.create');
        Route::get('/trucks/{truck}/edit', function (Truck $truck) {
            return view('pages.fleet.trucks.edit', ['truck' => $truck]);
        })->name('trucks.edit');

        Route::get('/drivers', DriverList::class)->name('drivers.index');
        Route::get('/drivers/create', function () {
            return view('pages.fleet.drivers.create');
        })->name('drivers.create');
        Route::get('/drivers/{driver}/edit', function (Driver $driver) {
            return view('pages.fleet.drivers.edit', ['driver' => $driver]);
        })->name('drivers.edit');

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
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', InvoiceList::class)->name('index');
            Route::get('/create', CreateInvoice::class)->name('create');
            Route::get('/{invoice}/edit', InvoiceForm::class)->whereNumber('invoice')->name('edit');
            Route::get('/{invoice}/electronic', ElectronicInvoiceForm::class)->whereNumber('invoice')->name('electronic');
        });

        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', PaymentList::class)->name('index');
            Route::get('/create', PaymentForm::class)->name('create');
            Route::get('/{payment}/edit', PaymentForm::class)->whereNumber('payment')->name('edit');
        });

        Route::middleware('can:viewAny,App\\Models\\Invoice')->group(function () {
            Route::get('/sunat-dashboard', SunatUnifiedDashboard::class)->name('sunat-dashboard');
            Route::get('/sunat-dashboard/export/excel', [SunatDashboardExportController::class, 'excel'])->name('sunat-dashboard.export.excel');
            Route::get('/sunat-dashboard/export/pdf', [SunatDashboardExportController::class, 'pdf'])->name('sunat-dashboard.export.pdf');
        });

        // GRE-T (transportista)
        Route::get('/gre-t', TransportGuideIndex::class)
            ->name('transport-guides.index')
            ->defaults('type', TransportGuide::TYPE_TRANSPORTISTA)
            ->can('viewAny', TransportGuide::class);

        Route::get('/gre-t/create', TransportGuideCreateController::class)
            ->name('transport-guides.create')
            ->defaults('type', TransportGuide::TYPE_TRANSPORTISTA)
            ->can('create', TransportGuide::class);

        // GRE-R (remitente)
        Route::get('/gre-r', TransportGuideIndex::class)
            ->name('remitter-guides.index')
            ->defaults('type', TransportGuide::TYPE_REMITENTE)
            ->can('viewAny', TransportGuide::class);

        Route::get('/gre-r/create', TransportGuideCreateController::class)
            ->name('remitter-guides.create')
            ->defaults('type', TransportGuide::TYPE_REMITENTE)
            ->can('create', TransportGuide::class);

        // Rutas de compatibilidad con enlaces existentes
        Route::redirect('/transport-guides', '/billing/gre-t');
        Route::redirect('/transport-guides/create', '/billing/gre-t/create');
        Route::redirect('/transport-guide/create', '/billing/gre-t/create')->name('transport-guide.create-redirect');

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

    Route::prefix('finance')->name('finance.')->middleware('can:viewAny,' . Transaction::class)->group(function () {
        Route::get('/transactions', TransactionList::class)
            ->name('transactions.index');
        Route::get('/transactions/analytics', TransactionAnalytics::class)
            ->name('transactions.analytics');
        Route::get('/collections-report', CollectionsAndExpensesReport::class)
            ->name('collections.report');
    });

    Route::middleware('signed')->group(function () {
        Route::get('/billing/invoices/{invoice}/download/xml', [InvoiceFileController::class, 'xml'])->name('billing.invoices.download.xml');
        Route::get('/billing/invoices/{invoice}/download/cdr', [InvoiceFileController::class, 'cdr'])->name('billing.invoices.download.cdr');
        Route::get('/billing/invoices/{invoice}/download/pdf', [InvoiceFileController::class, 'pdf'])->name('billing.invoices.download.pdf');

        Route::get('/billing/transport-guides/{transportGuide}/download/xml', [TransportGuideFileController::class, 'xml'])
            ->can('view', 'transportGuide')
            ->name('billing.transport-guides.download.xml');
        Route::get('/billing/transport-guides/{transportGuide}/download/cdr', [TransportGuideFileController::class, 'cdr'])
            ->can('view', 'transportGuide')
            ->name('billing.transport-guides.download.cdr');
        Route::get('/billing/transport-guides/{transportGuide}/download/pdf', [TransportGuideFileController::class, 'pdf'])
            ->can('view', 'transportGuide')
            ->name('billing.transport-guides.download.pdf');
    });

    Route::prefix('portal')->name('portal.')->middleware('can:view-dashboard.client')->group(function () {
        Route::get('/orders', ClientOrderTracker::class)->name('orders');
    });

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
});
