<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            if (DB::getDriverName() === 'pgsql') {
                $maxId = (int) (DB::table('ventas')->max('id') ?? 0);
                $next = $maxId + 1;

                // Para columnas identity en Postgres modernos, reiniciar con ALTER TABLE ... RESTART WITH
                try {
                    DB::statement("ALTER TABLE ventas ALTER COLUMN id RESTART WITH {$next}");
                } catch (\Throwable $e) {
                    // Fallback para columnas serial antiguas
                    DB::statement("SELECT setval(pg_get_serial_sequence('ventas','id'), {$next}, false)");
                }
            }
        } catch (\Throwable $e) {
            // Ignorar en entornos donde la tabla aún no existe o no es Postgres
        }
    }

    public function down(): void
    {
        // Sin acción: no es seguro revertir una sincronización de secuencia
    }
};
