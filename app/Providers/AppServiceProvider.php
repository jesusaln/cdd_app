<?php

namespace App\Providers;

use Inertia\Inertia;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // HTTPS forcing commented out for HTTP fix pack
        // if (app()->environment('production')) {
        //     URL::forceScheme('https');
        // }

        // Nota: Para manejar UTF-8 malformado, trata los datos de origen
        // (BD/strings) antes de pasarlos a Inertia. Evitamos usar métodos
        // inexistentes en la versión actual de inertia-laravel.
        // Registrar el evento y el listener
        Event::listen(
            \App\Events\ClientCreated::class, // El evento
            \App\Listeners\StoreClientNotification::class // El listener
        );

        // Mapeo polimórfico: usar alias cortos y permitir FQCN por compatibilidad
        // Activamos enforce para evitar nombres no mapeados en el futuro
        Relation::enforceMorphMap([
            // Aliases preferidos
            'producto' => \App\Models\Producto::class,
            'servicio' => \App\Models\Servicio::class,
            'cliente'  => \App\Models\Cliente::class,
            'prestamo' => \App\Models\Prestamo::class,
            'pago_prestamo' => \App\Models\PagoPrestamo::class,
            'historial_pago_prestamo' => \App\Models\HistorialPagoPrestamo::class,
            'venta' => \App\Models\Venta::class,
            // Nota: Para modelos de terceros como User/Tecnico usados por spatie/permission,
            // no definimos alias cortos para no romper pivotes existentes

            // Compatibilidad por si existen tipos almacenados con FQCN
            'App\\Models\\Producto' => \App\Models\Producto::class,
            'App\\Models\\Servicio' => \App\Models\Servicio::class,
            'App\\Models\\Cliente'  => \App\Models\Cliente::class,
            'App\\Models\\User' => \App\Models\User::class,
            'App\\Models\\Tecnico' => \App\Models\Tecnico::class,
            'App\\Models\\Prestamo' => \App\Models\Prestamo::class,
            'App\\Models\\PagoPrestamo' => \App\Models\PagoPrestamo::class,
            'App\\Models\\HistorialPagoPrestamo' => \App\Models\HistorialPagoPrestamo::class,
            'App\\Models\\Venta' => \App\Models\Venta::class,
        ]);
    }
}


