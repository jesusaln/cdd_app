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

        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tecnico_id')->constrained('tecnicos')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('tipo_servicio');
            $table->dateTime('fecha_hora');
            $table->text('descripcion')->nullable();
            $table->string('tipo_equipo');
            $table->string('marca_equipo');
            $table->string('modelo_equipo');
            $table->text('problema_reportado')->nullable();
            $table->string('estado')->default('pendiente'); // Nuevo campo de estado
            // Campos para las evidencias
            $table->text('evidencias')->nullable(); // Campo para texto de evidencias
            $table->string('foto_equipo')->nullable(); // Campo para la foto del equipo
            $table->string('foto_hoja_servicio')->nullable(); // Campo para la foto de la hoja de servicio
            $table->string('foto_identificacion')->nullable(); // Campo para la foto de identificación del cliente
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
