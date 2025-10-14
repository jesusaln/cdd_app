<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Seeder;

class UserNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            $this->command->warn('No hay usuarios en la base de datos. Crea un usuario primero.');
            return;
        }

        // Crear algunas notificaciones de prueba
        UserNotification::factory()->count(5)->create([
            'user_id' => $user->id,
            'read_at' => null, // No leídas
        ]);

        UserNotification::factory()->count(3)->create([
            'user_id' => $user->id,
            'read_at' => now(), // Leídas
        ]);

        // Crear una notificación específica de cliente nuevo
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'new_client',
            'title' => 'Cliente de Prueba Registrado',
            'message' => 'Se ha registrado el cliente "Juan Pérez" en el sistema',
            'data' => [
                'client_id' => 1,
                'client_name' => 'Juan Pérez',
                'client_email' => 'juan@example.com',
                'created_at' => now()->toISOString()
            ],
            'action_url' => '/clientes/1',
            'icon' => 'fas fa-user-plus',
            'read_at' => null,
        ]);

        $this->command->info('Notificaciones de prueba creadas exitosamente.');
        $this->command->info('Total de notificaciones: ' . UserNotification::count());
        $this->command->info('Notificaciones no leídas: ' . UserNotification::unread()->count());
    }
}
