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
            $table->decimal('margen_venta_productos', 5, 2)->default(0)->after('activo'); // porcentaje
            $table->decimal('margen_venta_servicios', 5, 2)->default(0)->after('margen_venta_productos'); // porcentaje
            $table->decimal('comision_instalacion', 8, 2)->default(0)->after('margen_venta_servicios'); // monto fijo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tecnicos', function (Blueprint $table) {
            $table->dropColumn(['margen_venta_productos', 'margen_venta_servicios', 'comision_instalacion']);
        });
    }
};
