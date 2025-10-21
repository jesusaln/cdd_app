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
        // Evitar error si ya existe en producción
        if (Schema::hasTable('unidades_medida')) {
            return;
        }
        Schema::create('unidades_medida', function (Blueprint $table) {
            $table->id();

            // Información básica
            $table->string('nombre');
            $table->string('abreviatura')->nullable();
            $table->text('descripcion')->nullable();

            // Estado
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('nombre');
            $table->index('estado');
            $table->unique('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidades_medida');
    }
};
