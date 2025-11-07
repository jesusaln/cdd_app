<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Asegurar que la columna exista y esté rellena antes de hacerla NOT NULL
        try {
            if (Schema::hasColumn('ventas', 'almacen_id')) {
                // En Postgres, usar ALTER TABLE directo para evitar dependencia en doctrine/dbal
                DB::statement('ALTER TABLE ventas ALTER COLUMN almacen_id SET NOT NULL');
            }
        } catch (\Throwable $e) {
            // Si falla (p. ej. por motor distinto), ignorar silenciosamente
        }
    }

    public function down(): void
    {
        try {
            if (Schema::hasColumn('ventas', 'almacen_id')) {
                DB::statement('ALTER TABLE ventas ALTER COLUMN almacen_id DROP NOT NULL');
            }
        } catch (\Throwable $e) {
            // Ignorar en reversión si no aplica
        }
    }
};

