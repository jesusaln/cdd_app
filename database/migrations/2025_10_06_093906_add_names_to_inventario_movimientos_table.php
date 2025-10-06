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
        Schema::table('inventario_movimientos', function (Blueprint $table) {
            $table->string('producto_nombre')->nullable()->after('producto_id');
            $table->string('almacen_nombre')->nullable()->after('almacen_id');
            $table->string('usuario_nombre')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventario_movimientos', function (Blueprint $table) {
            $table->dropColumn(['producto_nombre', 'almacen_nombre', 'usuario_nombre']);
        });
    }
};
