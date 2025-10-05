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
            $table->string('ubicacion', 255)->nullable(); // Ubicación del almacén
            $table->string('direccion')->nullable(); // Dirección completa
            $table->string('telefono', 20)->nullable(); // Teléfono de contacto
            $table->foreignId('responsable')->nullable()->constrained('users')->onDelete('set null'); // Usuario responsable
            $table->enum('estado', ['activo', 'inactivo'])->default('activo'); // Estado del almacén
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
