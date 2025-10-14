<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            // Índices para mejorar filtros/ordenamientos frecuentes
            $table->index('fecha');
            $table->index('proximo_mantenimiento');
            $table->index('estado');
            $table->index('tipo');

            // Evitar duplicados del mismo servicio en la misma fecha para el mismo carro
            $table->unique(['carro_id', 'tipo', 'fecha'], 'mto_carro_tipo_fecha_unique');
        });
    }

    public function down(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropIndex(['fecha']);
            $table->dropIndex(['proximo_mantenimiento']);
            $table->dropIndex(['estado']);
            $table->dropIndex(['tipo']);
            $table->dropUnique('mto_carro_tipo_fecha_unique');
        });
    }
};

