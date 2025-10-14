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
        // Asignar almacen_id a productos existentes que no lo tienen
        // Usar el primer almacén activo disponible
        $almacenId = DB::table('almacenes')->where('estado', 'activo')->orderBy('id')->first()?->id;

        if ($almacenId) {
            DB::table('productos')
                ->whereNull('almacen_id')
                ->update(['almacen_id' => $almacenId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertir, ya que asignar almacen_id es una corrección necesaria
    }
};
