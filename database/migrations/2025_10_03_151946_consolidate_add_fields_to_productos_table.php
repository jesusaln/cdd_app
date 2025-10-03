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
        Schema::table('productos', function (Blueprint $table) {
            $table->integer('reservado')->default(0)->after('stock');
            $table->decimal('margen_ganancia', 5, 2)->default(0)->after('precio_venta'); // porcentaje
            $table->decimal('comision_vendedor', 5, 2)->default(0)->after('margen_ganancia'); // porcentaje
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['reservado', 'margen_ganancia', 'comision_vendedor']);
        });
    }
};
