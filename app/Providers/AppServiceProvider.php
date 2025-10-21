<?php

namespace App\Providers;

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

        // Registrar el evento y el listener
        Event::listen(
            \App\Events\ClientCreated::class, // El evento
            \App\Listeners\StoreClientNotification::class // El listener
        );
    }
}
