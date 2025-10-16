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
        Schema::create('cita_servicios', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            $table->foreignId('servicio_id')->constrained('servicios')->onDelete('cascade');

            // Información del servicio realizado
            $table->integer('cantidad')->default(1);
            $table->decimal('precio', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->text('notas')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['cita_id', 'servicio_id']);
            $table->index(['servicio_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cita_servicios');
    }
};

