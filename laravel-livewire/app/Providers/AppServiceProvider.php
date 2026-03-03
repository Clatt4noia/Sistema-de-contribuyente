<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registrar servicios de aplicación.
     */
    public function register(): void
    {
        //
    }

    /**
     * Inicializar configuraciones globales.
     */
    public function boot(): void
    {
        // Registrar observer de órdenes
        \App\Models\Order::observe(\App\Observers\OrderObserver::class);
        \App\Models\Invoice::observe(\App\Observers\InvoiceObserver::class);
        \App\Models\Maintenance::observe(\App\Observers\MaintenanceObserver::class);


        Date::setLocale('es');
        Carbon::setLocale('es');

        if (function_exists('setlocale')) {
            setlocale(LC_TIME, 'es_PE.UTF-8', 'es_PE', 'es');
        }

        $disk = config('greenter.storage.disk_xml_cdr');
        $directories = array_filter([
            trim((string) config('greenter.storage.xml_directory'), '/'),
            trim((string) config('greenter.storage.cdr_directory'), '/'),
            trim((string) config('greenter.storage.pdf_directory'), '/'),
        ]);

        try {
            $filesystem = Storage::disk($disk);

            foreach ($directories as $directory) {
                if (! $filesystem->exists($directory)) {
                    $filesystem->makeDirectory($directory);
                }
            }
        } catch (Throwable $exception) {
            report($exception);
        }

    }
}
