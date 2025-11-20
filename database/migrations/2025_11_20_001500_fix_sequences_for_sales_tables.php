<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $tables = [
            'ventas',
            'cuentas_por_cobrar',
            'venta_items',
        ];

        foreach ($tables as $table) {
            try {
                // Crear secuencia si no existe
                $seq = $table . '_id_seq';
                DB::statement("CREATE SEQUENCE IF NOT EXISTS {$seq};");
                DB::statement("ALTER SEQUENCE {$seq} OWNED BY {$table}.id;");
                DB::statement("ALTER TABLE {$table} ALTER COLUMN id SET DEFAULT nextval('{$seq}');");

                // Calcular siguiente id y ajustar secuencia
                $maxId = (int) (DB::table($table)->max('id') ?? 0);
                $next = $maxId + 1;
                DB::statement("SELECT setval('{$seq}', {$next}, false);");
            } catch (\Throwable $e) {
                // Continuar con las demás tablas aunque alguna falle
            }
        }
    }

    public function down(): void
    {
        // No se revertirá para no dejar columnas sin default/sequence
    }
};

