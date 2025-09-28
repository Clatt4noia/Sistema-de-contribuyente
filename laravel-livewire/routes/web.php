<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

use App\Livewire\Fleet\AssignmentForm;
use App\Livewire\Fleet\AssignmentList;
use App\Livewire\Fleet\DriverForm;
use App\Livewire\Fleet\DriverList;
use App\Livewire\Fleet\MaintenanceForm;
use App\Livewire\Fleet\MaintenanceList;
use App\Livewire\Fleet\TruckForm;
use App\Livewire\Fleet\TruckList;
use App\Livewire\Fleet\Report as FleetReport;
use App\Livewire\Orders\OrderForm;
use App\Livewire\Orders\OrderList;
use App\Livewire\Clients\ClientForm;
use App\Livewire\Clients\ClientList;
use App\Livewire\Billing\InvoiceForm;
use App\Livewire\Billing\InvoiceList;
use App\Livewire\Billing\PaymentForm;
use App\Livewire\Billing\PaymentList;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::redirect('/home', '/')->name('home');

    Route::prefix('fleet')->name('fleet.')->group(function () {
        Route::get('/trucks', TruckList::class)->name('trucks.index');
        Route::get('/trucks/create', TruckForm::class)->name('trucks.create');
        Route::get('/trucks/{truck}/edit', TruckForm::class)->name('trucks.edit');

        Route::get('/drivers', DriverList::class)->name('drivers.index');
        Route::get('/drivers/create', DriverForm::class)->name('drivers.create');
        Route::get('/drivers/{driver}/edit', DriverForm::class)->name('drivers.edit');

        Route::get('/maintenance', MaintenanceList::class)->name('maintenance.index');
        Route::get('/maintenance/create', MaintenanceForm::class)->name('maintenance.create');
        Route::get('/maintenance/{id}/edit', MaintenanceForm::class)->name('maintenance.edit');

        Route::get('/assignments', AssignmentList::class)->name('assignments.index');
        Route::get('/assignments/create', AssignmentForm::class)->name('assignments.create');
        Route::get('/assignments/{id}/edit', AssignmentForm::class)->name('assignments.edit');
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
        Route::get('/invoices/create', InvoiceForm::class)->name('invoices.create');
        Route::get('/invoices/{invoice}/edit', InvoiceForm::class)->name('invoices.edit');

        Route::get('/payments', PaymentList::class)->name('payments.index');
        Route::get('/payments/create', PaymentForm::class)->name('payments.create');
        Route::get('/payments/{payment}/edit', PaymentForm::class)->name('payments.edit');
    });

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
});
