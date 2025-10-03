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
        // Add fields to compras
        Schema::table('compras', function (Blueprint $table) {
            $table->date('fecha_compra')->nullable()->after('numero_compra');
            $table->foreignId('orden_compra_id')->nullable()->constrained('orden_compras')->nullOnDelete()->after('fecha_compra');
        });

        // Add fields to orden_compra_producto
        Schema::table('orden_compra_producto', function (Blueprint $table) {
            $table->string('unidad_medida')->nullable()->after('descuento');
        });

        // Add fields to venta_items
        Schema::table('venta_items', function (Blueprint $table) {
            $table->decimal('costo_unitario', 10, 2)->nullable()->after('descuento_monto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove fields from compras
        Schema::table('compras', function (Blueprint $table) {
            $table->dropForeign(['orden_compra_id']);
            $table->dropColumn(['fecha_compra', 'orden_compra_id']);
        });

        // Remove fields from orden_compra_producto
        Schema::table('orden_compra_producto', function (Blueprint $table) {
            $table->dropColumn('unidad_medida');
        });

        // Remove fields from venta_items
        Schema::table('venta_items', function (Blueprint $table) {
            $table->dropColumn('costo_unitario');
        });
    }
};
