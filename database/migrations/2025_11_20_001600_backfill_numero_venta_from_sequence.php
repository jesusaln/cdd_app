<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Solo Postgres
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        try {
            $ventas = DB::table('ventas')
                ->orderBy('id')
                ->get(['id', 'numero_venta']);

            $n = 1;
            foreach ($ventas as $venta) {
                $nuevo = 'V' . str_pad($n, 4, '0', STR_PAD_LEFT);
                DB::table('ventas')
                    ->where('id', $venta->id)
                    ->update(['numero_venta' => $nuevo]);
                $n++;
            }
        } catch (\Throwable $e) {
            // Silenciar para no interrumpir despliegue si falla
        }
    }

    public function down(): void
    {
        // No revertimos numeración
    }
};

