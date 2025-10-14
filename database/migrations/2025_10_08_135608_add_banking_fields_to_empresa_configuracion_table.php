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
        Schema::table('empresa_configuracion', function (Blueprint $table) {
            // Solo agregar columnas adicionales que no existen
            if (!Schema::hasColumn('empresa_configuracion', 'numero_cuenta')) {
                $table->string('numero_cuenta')->nullable()->after('titular');
            }
            if (!Schema::hasColumn('empresa_configuracion', 'numero_tarjeta')) {
                $table->string('numero_tarjeta')->nullable()->after('numero_cuenta');
            }
            if (!Schema::hasColumn('empresa_configuracion', 'nombre_titular')) {
                $table->string('nombre_titular')->nullable()->after('numero_tarjeta');
            }
            if (!Schema::hasColumn('empresa_configuracion', 'informacion_adicional_bancaria')) {
                $table->text('informacion_adicional_bancaria')->nullable()->after('nombre_titular');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresa_configuracion', function (Blueprint $table) {
            // Solo eliminar columnas que esta migración creó
            if (Schema::hasColumn('empresa_configuracion', 'informacion_adicional_bancaria')) {
                $table->dropColumn('informacion_adicional_bancaria');
            }
            if (Schema::hasColumn('empresa_configuracion', 'nombre_titular')) {
                $table->dropColumn('nombre_titular');
            }
            if (Schema::hasColumn('empresa_configuracion', 'numero_tarjeta')) {
                $table->dropColumn('numero_tarjeta');
            }
            if (Schema::hasColumn('empresa_configuracion', 'numero_cuenta')) {
                $table->dropColumn('numero_cuenta');
            }
        });
    }
};
