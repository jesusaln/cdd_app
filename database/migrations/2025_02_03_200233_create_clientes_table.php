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

            $table->string('nombre_razon_social', 255);
            $table->enum('tipo_persona', ['fisica', 'moral']);

            $table->string('tipo_identificacion', 20)->nullable();
            $table->string('identificacion', 50)->nullable();
            $table->string('curp', 18)->nullable();

            $table->string('rfc', 13)->unique()->comment('12 moral, 13 física');

            // Fiscales (claves SAT)
            $table->char('regimen_fiscal', 3);
            $table->char('uso_cfdi', 3);

            // Contacto
            $table->string('email', 255);
            $table->string('telefono', 20)->nullable();

            // Dirección MX
            $table->string('calle', 150);
            $table->string('numero_exterior', 30);
            $table->string('numero_interior', 30)->nullable();
            $table->string('colonia', 150);
            $table->char('codigo_postal', 5);
            $table->string('municipio', 120);
            $table->char('estado', 3)->comment('SAT c_Estado');
            $table->char('pais', 2)->default('MX');

            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();

            // Índices
            $table->index(['nombre_razon_social']);
            $table->index(['email']);
            $table->index(['activo']);
            $table->index(['created_at']);
            $table->fullText(['nombre_razon_social', 'email', 'rfc']);

            // CHECKS (MySQL 8+)
            $table->check("pais = 'MX'");
            $table->check("codigo_postal REGEXP '^[0-9]{5}$'");
            $table->check("rfc REGEXP '^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$'");
            $table->check("curp IS NULL OR curp REGEXP '^[A-Z][AEIOU][A-Z]{2}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$'");
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->foreign('regimen_fiscal')->references('clave')->on('sat_regimenes_fiscales');
            $table->foreign('uso_cfdi')->references('clave')->on('sat_usos_cfdi');
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
