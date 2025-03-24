<?php

namespace App\Listeners;

use App\Events\ClientCreated;
use App\Models\Notification;

class StoreClientNotification
{
    public function handle(ClientCreated $event)
    {
        // Verifica si ya existe una notificación para este cliente
     /*
    if (!Notification::where('data->client_id', $event->cliente->id)->exists()) {
        Notification::create([
            'type' => 'new_client',
            'data' => [
                'client_name' => $event->cliente->nombre_razon_social,
                'client_id' => $event->cliente->id,
            ],
            'read' => false,
        ]);
    }
    */
}
