<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Nueva migración consolidada para tablas de categorías
     */
    public function up(): void
    {
        // Crear tabla de categorías para productos
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            // Índices de rendimiento
            $table->index('nombre');
            $table->index('estado');
        });

        // Crear tabla de categorías para herramientas
        Schema::create('categoria_herramientas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índices de rendimiento
            $table->index('nombre');
            $table->index('slug');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_herramientas');
        Schema::dropIfExists('categorias');
    }
};
