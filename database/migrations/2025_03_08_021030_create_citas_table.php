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
