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


        Date::useLocale('es');
        Carbon::setLocale('es');

        if (function_exists('setlocale')) {
            setlocale(LC_TIME, 'es_PE.UTF-8', 'es_PE', 'es');
        }

        $disk = config('billing.storage.disk_xml_cdr');
        $directories = array_filter([
            trim((string) config('billing.storage.xml_directory'), '/'),
            trim((string) config('billing.storage.cdr_directory'), '/'),
            trim((string) config('billing.storage.pdf_directory'), '/'),
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
