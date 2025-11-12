<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles base (si existe la tabla roles)
        if (Schema::hasTable('roles')) {
            $roles = [
                ['name' => 'admin', 'guard_name' => 'web'],
                ['name' => 'editor', 'guard_name' => 'web'],
            ];

            foreach ($roles as $role) {
                Role::firstOrCreate($role);
            }
        }

        // Crear usuario administrador por defecto (si existe tabla users)
        if (Schema::hasTable('users')) {
            $email = 'admin@cdd.local';

            $user = DB::table('users')->where('email', $email)->first();
            if (!$user) {
                $id = DB::table('users')->insertGetId([
                    'name' => 'Administrador',
                    'email' => $email,
                    'password' => Hash::make('Admin123!'),
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Asignar rol admin si está disponible (tablas de spatie deben existir)
                if (Schema::hasTable('roles') && Schema::hasTable('model_has_roles')) {
                    $adminRole = Role::where('name', 'admin')->first();
                    if ($adminRole) {
                        DB::table('model_has_roles')->insert([
                            'role_id' => $adminRole->id,
                            'model_type' => \App\Models\User::class,
                            'model_id' => $id,
                        ]);
                    }
                }
            }
        }
    }
}

