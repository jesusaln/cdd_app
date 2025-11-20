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

            $maxId = (int) (DB::table('cuentas_por_cobrar')->max('id') ?? 0);
            $next = $maxId + 1;

            try {
                DB::statement("ALTER TABLE cuentas_por_cobrar ALTER COLUMN id RESTART WITH {$next}");
            } catch (\Throwable $e) {
                DB::statement("SELECT setval(pg_get_serial_sequence('cuentas_por_cobrar','id'), {$next}, false)");
            }
        } catch (\Throwable $e) {
            // Silenciar si la tabla no existe o el motor no es Postgres
        }
    }

    public function down(): void
    {
        // No revertimos ajustes de secuencia
    }
};

