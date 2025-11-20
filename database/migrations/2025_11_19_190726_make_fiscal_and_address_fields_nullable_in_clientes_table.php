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
        Schema::table('clientes', function (Blueprint $table) {
            // Campos fiscales
            $table->string('rfc', 13)->nullable()->change();
            $table->char('regimen_fiscal', 3)->nullable()->change();
            $table->char('uso_cfdi', 3)->nullable()->change();

            // Campos de dirección
            $table->string('calle', 150)->nullable()->change();
            $table->string('numero_exterior', 30)->nullable()->change();
            $table->string('colonia', 150)->nullable()->change();
            $table->char('codigo_postal', 5)->nullable()->change();
            $table->string('municipio', 120)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Revertir cambios (cuidado con datos existentes)
            $table->string('rfc', 13)->nullable(false)->change();
            $table->char('regimen_fiscal', 3)->nullable(false)->change();
            $table->char('uso_cfdi', 3)->nullable(false)->change();

            $table->string('calle', 150)->nullable(false)->change();
            $table->string('numero_exterior', 30)->nullable(false)->change();
            $table->string('colonia', 150)->nullable(false)->change();
            $table->char('codigo_postal', 5)->nullable(false)->change();
            $table->string('municipio', 120)->nullable(false)->change();
        });
    }
};
