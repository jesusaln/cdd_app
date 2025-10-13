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
        // La columna prioridad ya existe, no necesitamos hacer nada
        // Esta migración fue creada para solucionar el problema original
        // pero la columna ya está presente en la base de datos
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No eliminamos la columna prioridad ya que puede ser necesaria
        // para el funcionamiento correcto de la aplicación
    }
};
