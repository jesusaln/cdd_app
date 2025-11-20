<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Nueva migración consolidada para tabla almacenes
     */
    public function up(): void
    {
        // Si la tabla ya existe (entornos instalados), no recrearla
        if (Schema::hasTable('almacenes')) {
            return;
        }

        Schema::create('almacenes', function (Blueprint $table) {
            $table->id();

            // Información básica del almacén
            $table->string('nombre', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->string('ubicacion', 255)->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono', 20)->nullable();

            // Responsable del almacén
            $table->foreignId('responsable')->nullable()->constrained('users')->nullOnDelete();

            // Estado del almacén
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');

            $table->timestamps();

            // Índices de rendimiento
            $table->index('nombre');
            $table->index('estado');
            $table->index(['estado', 'responsable']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('almacenes');
    }
};

