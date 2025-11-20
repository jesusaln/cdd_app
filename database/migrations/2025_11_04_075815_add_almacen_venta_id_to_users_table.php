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
        Schema::table('users', function (Blueprint $table) {
            // Evitar errores si la columna ya existe en entornos parcialmente migrados
            if (!Schema::hasColumn('users', 'almacen_venta_id')) {
                $table->foreignId('almacen_venta_id')
                    ->nullable()
                    ->constrained('almacenes')
                    ->onDelete('set null')
                    ->after('activo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['almacen_venta_id']);
            $table->dropColumn('almacen_venta_id');
        });
    }
};
