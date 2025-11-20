<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            if (DB::getDriverName() !== 'pgsql') {
                return;
            }

            $maxId = (int) (DB::table('venta_items')->max('id') ?? 0);
            $next = $maxId + 1;

            try {
                DB::statement("ALTER TABLE venta_items ALTER COLUMN id RESTART WITH {$next}");
            } catch (\Throwable $e) {
                DB::statement("SELECT setval(pg_get_serial_sequence('venta_items','id'), {$next}, false)");
            }
        } catch (\Throwable $e) {
            // Silenciar en entornos donde la tabla no exista o no sea Postgres
        }
    }

    public function down(): void
    {
        // No revertimos ajustes de secuencia
    }
};

