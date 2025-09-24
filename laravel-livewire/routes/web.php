<?php

use Livewire\Volt\Volt;

// Incluir rutas de autenticación
require __DIR__.'/auth.php';
use App\Livewire\Fleet\AssignmentForm;
use App\Livewire\Fleet\AssignmentList;
use App\Livewire\Fleet\DriverForm;
use App\Livewire\Fleet\DriverList;
use App\Livewire\Fleet\MaintenanceForm;
use App\Livewire\Fleet\MaintenanceList;
use App\Livewire\Fleet\TruckForm;
use App\Livewire\Fleet\TruckList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Definiendo la ruta 'home' como alias de 'dashboard'
    Route::redirect('/home', '/')->name('home');

    // Rutas para el módulo de Flota
    Route::prefix('fleet')->name('fleet.')->group(function () {
        // Rutas para Camiones
        Route::get('/trucks', TruckList::class)->name('trucks.index');
        Route::get('/trucks/create', TruckForm::class)->name('trucks.create');
        Route::get('/trucks/{truck}/edit', TruckForm::class)->name('trucks.edit');
        
        // Rutas para Choferes
        Route::get('/drivers', DriverList::class)->name('drivers.index');
        Route::get('/drivers/create', DriverForm::class)->name('drivers.create');
        Route::get('/drivers/{driver}/edit', DriverForm::class)->name('drivers.edit');
        
        // Rutas para Mantenimiento
        Route::get('/maintenance', MaintenanceList::class)->name('maintenance.index');
        Route::get('/maintenance/create', MaintenanceForm::class)->name('maintenance.create');
        Route::get('/maintenance/{id}/edit', MaintenanceForm::class)->name('maintenance.edit');
        
        // Rutas para Asignaciones
        Route::get('/assignments', AssignmentList::class)->name('assignments.index');
        Route::get('/assignments/create', AssignmentForm::class)->name('assignments.create');
        Route::get('/assignments/{id}/edit', AssignmentForm::class)->name('assignments.edit');
    });

    // Rutas para configuraciones
    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
});
