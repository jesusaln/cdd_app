<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AddAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuario administrador específico
        if (Schema::hasTable('users')) {
            $email = 'jesuslopeznoriega@hotmail.com';

            $user = DB::table('users')->where('email', $email)->first();
            if (!$user) {
                $id = DB::table('users')->insertGetId([
                    'name' => 'Jesus Lopez Noriega',
                    'email' => $email,
                    'password' => Hash::make('Zl01kpContpaqi1'),
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Asignar rol admin si está disponible
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