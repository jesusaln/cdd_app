<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\ClientCreated::class => [
            \App\Listeners\StoreClientNotification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
