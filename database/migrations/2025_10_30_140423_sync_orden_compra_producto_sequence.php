<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Realinea la secuencia del ID en PostgreSQL para evitar llaves duplicadas
        // Sube la secuencia al MAX(id) actual; si la tabla está vacía, la marca como no llamada
        DB::statement(
            "SELECT setval(pg_get_serial_sequence('orden_compra_producto','id'), COALESCE(MAX(id), 1), MAX(id) IS NOT NULL) FROM orden_compra_producto"
        );
    }

    public function down(): void
    {
        // No revertimos este ajuste de secuencia automáticamente
    }
};
