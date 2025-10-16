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
        Schema::table('tecnicos', function (Blueprint $table) {
            // Modificar columnas de margen para asegurar valores por defecto y no nulos
            $table->decimal('margen_venta_productos', 5, 2)->default(0)->nullable(false)->change();
            $table->decimal('margen_venta_servicios', 5, 2)->default(0)->nullable(false)->change();
            $table->decimal('comision_instalacion', 8, 2)->default(0)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tecnicos', function (Blueprint $table) {
            // Revertir cambios - permitir valores nulos nuevamente
            $table->decimal('margen_venta_productos', 5, 2)->default(0)->nullable()->change();
            $table->decimal('margen_venta_servicios', 5, 2)->default(0)->nullable()->change();
            $table->decimal('comision_instalacion', 8, 2)->default(0)->nullable()->change();
        });
    }
};
