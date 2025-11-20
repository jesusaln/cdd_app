<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            if (DB::getDriverName() === 'pgsql' && DB::table('ventas')->exists()) {
                $maxId = (int) (DB::table('ventas')->max('id') ?? 0);
                $next = $maxId + 1;

                // Reiniciar secuencia/identity al siguiente id disponible
                try {
                    DB::statement("ALTER TABLE ventas ALTER COLUMN id RESTART WITH {$next}");
                } catch (\Throwable $e) {
                    DB::statement("SELECT setval(pg_get_serial_sequence('ventas','id'), {$next}, false)");
                }
            }
        } catch (\Throwable $e) {
            // Silenciar en entornos donde la tabla no exista
        }
    }

    public function down(): void
    {
        // No revertimos el ajuste de secuencia
    }
};

