<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Definir los permisos
        $permissions = [
            'view usuarios',
            'create usuarios',
            'edit usuarios',
            'delete usuarios',
        ];

        // Crear los permisos si no existen
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web', // Asegurar que el guard es correcto
            ]);
        }

        // Crear roles si no existen
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Asignar permisos después de crearlos
        $adminRole->syncPermissions($permissions);
        $userRole->syncPermissions(['view usuarios']);

        $this->command->info('Roles y permisos creados exitosamente.');

         // Crear un usuario con el rol de administrador
         $user = User::factory()->create([
            'name' => 'Test Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpassword'), // Contraseña para el usuario
        ]);

        // Asignar el rol de admin al usuario
        $user->assignRole($adminRole);
    }
}
