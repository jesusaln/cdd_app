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
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->string('codigo', 20)->unique();
            $table->foreignId('categoria_id')->constrained()->onDelete('cascade');
            $table->decimal('precio', 10, 2); // Aumentado a 10 dígitos para mayor flexibilidad
            $table->integer('duracion')->comment('Duración en minutos');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index('nombre');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
