<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear registros iniciales en inventarios para productos existentes
        // Solo para productos que no tienen registro en inventarios para su almacen_id
        DB::statement('
            INSERT INTO inventarios (producto_id, almacen_id, cantidad, stock_minimo, created_at, updated_at)
            SELECT p.id, p.almacen_id, p.stock, 10, datetime(\'now\'), datetime(\'now\')
            FROM productos p
            WHERE p.almacen_id IS NOT NULL
            AND NOT EXISTS (
                SELECT 1 FROM inventarios i
                WHERE i.producto_id = p.id AND i.almacen_id = p.almacen_id
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertir, ya que es una corrección necesaria
    }
};
