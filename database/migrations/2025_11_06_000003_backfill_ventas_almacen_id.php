<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Asignar almacen_id desde users.almacen_venta_id cuando el vendedor sea un User
        try {
            DB::statement(
                "UPDATE ventas v
                 SET almacen_id = u.almacen_venta_id
                 FROM users u
                 WHERE v.almacen_id IS NULL
                   AND v.vendedor_type = 'App\\\\Models\\\\User'
                   AND v.vendedor_id = u.id
                   AND u.almacen_venta_id IS NOT NULL"
            );
        } catch (\Throwable $e) {
            // Si falla por no existir alguna tabla/columna en entornos parciales, ignorar
        }

        // 2) Fallback: asignar un almacén activo por defecto a las ventas que sigan en NULL
        try {
            $defaultAlmacenId = DB::table('almacenes')
                ->where('estado', 'activo')
                ->orderBy('id')
                ->value('id');

            if (!$defaultAlmacenId) {
                $defaultAlmacenId = DB::table('almacenes')->min('id');
            }

            if ($defaultAlmacenId) {
                DB::table('ventas')
                    ->whereNull('almacen_id')
                    ->update(['almacen_id' => $defaultAlmacenId]);
            }
        } catch (\Throwable $e) {
            // Ignorar si no hay almacenes disponibles aún
        }
    }

    public function down(): void
    {
        // Dejar como estaba: volver a NULL solo lo que fue rellenado sería riesgoso sin un rastro
    }
};

