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
            // Eliminar campo direccion
            $table->dropColumn('direccion');

            // Agregar nuevos campos de dirección
            $table->string('calle')->nullable()->after('pais');
            $table->string('numero_exterior')->nullable()->after('calle');
            $table->string('numero_interior')->nullable()->after('numero_exterior');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresa_configuracion', function (Blueprint $table) {
            // Restaurar campo direccion
            $table->text('direccion')->nullable()->after('pais');

            // Eliminar nuevos campos
            $table->dropColumn(['calle', 'numero_exterior', 'numero_interior']);
        });
    }
};
