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
        Schema::create('registro_vacaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->year('anio'); // Año del registro de vacaciones
            $table->integer('dias_correspondientes'); // Días que le corresponden según antigüedad
            $table->integer('dias_disponibles'); // Días disponibles al inicio del año (acumulados del año anterior)
            $table->integer('dias_utilizados'); // Días utilizados en vacaciones aprobadas
            $table->integer('dias_pendientes'); // Días pendientes de usar (no utilizados)
            $table->integer('dias_acumulados_siguiente'); // Días que se acumulan para el siguiente año
            $table->date('fecha_calculo')->nullable(); // Fecha en que se calculó este registro
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'anio']); // Un registro por empleado por año
            $table->index(['user_id', 'anio']);
            $table->index('anio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_vacaciones');
    }
};
