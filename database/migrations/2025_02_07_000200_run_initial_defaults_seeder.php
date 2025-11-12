<?php

use Illuminate\Database\Migrations\Migration;
use Database\Seeders\InitialDefaultsSeeder;

return new class extends Migration
{
    public function up(): void
    {
        // Ejecutar el seeder de valores iniciales
        (new InitialDefaultsSeeder())->run();
    }

    public function down(): void
    {
        // Revertir datos sembrados de forma segura
        try {
            \Illuminate\Support\Facades\DB::table('almacenes')
                ->where('nombre', 'Almacén Principal')
                ->delete();
        } catch (\Throwable $e) {
            // Ignorar si la tabla no existe
        }

        try {
            \Illuminate\Support\Facades\DB::table('categorias')
                ->where('nombre', 'General')
                ->delete();
        } catch (\Throwable $e) {
        }

        try {
            \Illuminate\Support\Facades\DB::table('marcas')
                ->where('nombre', 'Genérica')
                ->delete();
        } catch (\Throwable $e) {
        }
    }
};

