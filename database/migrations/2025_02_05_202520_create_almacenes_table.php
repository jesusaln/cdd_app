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
        Schema::create('almacenes', function (Blueprint $table) {
            $table->id(); // ID autoincremental
            $table->string('nombre', 100)->unique(); // Nombre del almacén (único)
            $table->text('descripcion')->nullable(); // Descripción opcional
            $table->string('direccion', 255)->nullable(); // Dirección del almacén
            $table->timestamps(); // Columnas created_at y updated_at
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
