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
        Schema::create('equipo_renta', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('renta_id')->constrained('rentas')->onDelete('cascade');
            $table->foreignId('equipo_id')->constrained('equipos')->onDelete('cascade');

            // Precio específico para esta renta
            $table->decimal('precio_mensual', 10, 2);

            // Timestamps
            $table->timestamps();

            // Índice único para evitar duplicados
            $table->unique(['renta_id', 'equipo_id']);

            // Índices para optimización
            $table->index(['renta_id']);
            $table->index(['equipo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipo_renta');
    }
};
