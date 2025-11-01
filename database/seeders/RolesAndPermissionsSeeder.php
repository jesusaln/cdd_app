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
            // Permisos para clientes
            'view clientes',
            'create clientes',
            'edit clientes',
            'delete clientes',
            'export clientes',
            'stats clientes',
            // Permisos para respaldos de base de datos
            'manage-backups',
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
        $ventasRole = Role::firstOrCreate(['name' => 'ventas', 'guard_name' => 'web']);

        // Asignar permisos después de crearlos
        $adminRole->syncPermissions($permissions);
        $userRole->syncPermissions([
            'view usuarios',
            'view clientes',
            'create clientes',
            'edit clientes',
            'export clientes',
            'stats clientes'
        ]);
        $ventasRole->syncPermissions([
            'view clientes',
            'create clientes',
            'edit clientes',
            'export clientes',
            'stats clientes'
        ]);

        $this->command->info('Roles y permisos creados exitosamente.');

        // Crear o encontrar el usuario administrador
        $user = User::firstOrCreate(
            ['email' => 'jesuslopeznoriega@hotmail.com'],
            [
                'name' => 'Jesus Lopez',
                'password' => bcrypt('Zl01kpContpaqi1.'),
            ]
        );

        // Asignar el rol de admin al usuario (si no lo tiene ya)
        if (!$user->hasRole('admin')) {
            $user->assignRole($adminRole);
        }
    }
}
