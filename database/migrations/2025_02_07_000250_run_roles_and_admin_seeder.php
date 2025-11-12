<?php

use Illuminate\Database\Migrations\Migration;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        (new RolesAndAdminSeeder())->run();
    }

    public function down(): void
    {
        // Eliminar usuario admin por correo conocido
        try {
            $user = DB::table('users')->where('email', 'admin@cdd.local')->first();
            if ($user) {
                // Quitar roles asignados vía tabla pivot si existe
                if (\Illuminate\Support\Facades\Schema::hasTable('model_has_roles')) {
                    DB::table('model_has_roles')
                        ->where('model_type', \App\Models\User::class)
                        ->where('model_id', $user->id)
                        ->delete();
                }
                DB::table('users')->where('id', $user->id)->delete();
            }
        } catch (\Throwable $e) {
            // ignorar
        }

        // Eliminar roles creados
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('roles')) {
                DB::table('roles')->whereIn('name', ['admin', 'editor'])->delete();
            }
        } catch (\Throwable $e) {
            // ignorar
        }
    }
};

