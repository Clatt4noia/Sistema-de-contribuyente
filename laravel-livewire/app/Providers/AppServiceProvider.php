<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;

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

<<<<<<< HEAD
        // ✅ Configurar localización en español (Perú)
        Carbon::setLocale('es');
        Date::setLocale('es');

        if (function_exists('setlocale')) {
            setlocale(LC_TIME, 'es_PE.UTF-8', 'es_ES.UTF-8', 'es');
        }

        // ✅ Zona horaria Perú
        config(['app.timezone' => 'America/Lima']);
        date_default_timezone_set('America/Lima');

=======
        Date::useLocale('es');
        Carbon::setLocale('es');

        if (function_exists('setlocale')) {
            setlocale(LC_TIME, 'es_PE.UTF-8', 'es_PE', 'es');
        }
>>>>>>> 60f7131 (Documentar plan de integración SUNAT para facturación)
    }
}
