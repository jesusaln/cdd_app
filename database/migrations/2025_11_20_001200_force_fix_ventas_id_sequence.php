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

            // Calcular el siguiente id disponible
            $maxId = (int) (DB::table('ventas')->max('id') ?? 0);
            $next = $maxId + 1;

            // Ajustar la secuencia/identity al valor correcto
            try {
                DB::statement("ALTER TABLE ventas ALTER COLUMN id RESTART WITH {$next}");
            } catch (\Throwable $e) {
                DB::statement("SELECT setval(pg_get_serial_sequence('ventas','id'), {$next}, false)");
            }
        } catch (\Throwable $e) {
            // Silenciar en entornos sin la tabla o sin soporte
        }
    }

    public function down(): void
    {
        // No revertimos ajustes de secuencia
    }
};

