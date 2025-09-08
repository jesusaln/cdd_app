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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_razon_social'); // Nombre o razón social
            $table->string('tipo_persona'); // fisica|moral
            $table->string('tipo_identificacion')->nullable();
            $table->string('identificacion')->nullable();
            $table->string('curp')->nullable();
            $table->string('rfc', 13)->unique(); // 12 moral / 13 fisica (cabe)
            $table->string('regimen_fiscal'); // clave SAT
            $table->string('uso_cfdi'); // clave SAT
            $table->string('email'); // NO unique (puede repetirse)
            $table->string('telefono')->nullable();
            $table->string('calle');
            $table->string('numero_exterior');
            $table->string('numero_interior')->nullable();
            $table->string('colonia');
            $table->string('codigo_postal', 5);
            $table->string('municipio');
            $table->string('estado');
            $table->string('pais')->default('México');
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // índices útiles para búsquedas/orden (opcionales pero recomendables)
            $table->index(['nombre_razon_social']);
            $table->index(['email']);
            $table->index(['activo']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
