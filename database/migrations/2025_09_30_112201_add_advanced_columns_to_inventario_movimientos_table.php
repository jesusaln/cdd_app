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
            // Agregar columnas avanzadas para el sistema mejorado de inventario
            if (!Schema::hasColumn('inventario_movimientos', 'stock_anterior')) {
                $table->integer('stock_anterior')->nullable()->after('cantidad');
            }

            if (!Schema::hasColumn('inventario_movimientos', 'stock_posterior')) {
                $table->integer('stock_posterior')->nullable()->after('stock_anterior');
            }

            if (!Schema::hasColumn('inventario_movimientos', 'referencia_type')) {
                $table->string('referencia_type')->nullable()->after('motivo');
            }

            if (!Schema::hasColumn('inventario_movimientos', 'referencia_id')) {
                $table->unsignedBigInteger('referencia_id')->nullable()->after('referencia_type');
            }

            if (!Schema::hasColumn('inventario_movimientos', 'detalles')) {
                $table->json('detalles')->nullable()->after('referencia_id');
            }

            // Crear índice para referencias polimórficas
            if (!Schema::hasIndex('inventario_movimientos', ['referencia_type', 'referencia_id'])) {
                $table->index(['referencia_type', 'referencia_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventario_movimientos', function (Blueprint $table) {
            // Eliminar columnas agregadas
            $table->dropColumn([
                'stock_anterior',
                'stock_posterior',
                'referencia_type',
                'referencia_id',
                'detalles'
            ]);
        });
    }
};
