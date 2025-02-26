<?php

namespace App\Listeners;

use App\Events\ClientCreated;
use App\Models\Notification;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StoreClientNotification
{
    public function handle(ClientCreated $event)
    {
        Notification::create([
            'type' => 'new_client',
            'data' => [
                'client_name' => $event->client->name,
                'client_id' => $event->client->id,
            ],
            'read' => false,
        ]);
    }
}
