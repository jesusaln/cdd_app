<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventarios', function (Blueprint $table) {
            // Solo agregar almacen_id si no existe
            if (!Schema::hasColumn('inventarios', 'almacen_id')) {
                $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('cascade')->after('producto_id');
            }
            
            // Solo agregar stock_minimo si no existe
            if (!Schema::hasColumn('inventarios', 'stock_minimo')) {
                $table->integer('stock_minimo')->default(0)->after('cantidad');
            }
            
            // Solo agregar índice único si no existe
            if (!Schema::hasIndex('inventarios', ['producto_id', 'almacen_id'])) {
                $table->unique(['producto_id', 'almacen_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventarios', function (Blueprint $table) {
            $table->dropForeign(['almacen_id']);
            $table->dropColumn(['almacen_id', 'stock_minimo']);
            $table->dropUnique(['producto_id', 'almacen_id']);
        });
    }
};
