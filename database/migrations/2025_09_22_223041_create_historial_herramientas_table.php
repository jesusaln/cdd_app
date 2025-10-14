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
        Schema::create('historial_herramientas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('herramienta_id')->constrained('herramientas')->onDelete('cascade');
            $table->foreignId('tecnico_id')->constrained('tecnicos')->onDelete('cascade');
            $table->timestamp('fecha_asignacion');
            $table->timestamp('fecha_devolucion')->nullable();
            $table->foreignId('asignado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('recibido_por')->nullable()->constrained('users')->onDelete('set null');
            $table->text('observaciones_asignacion')->nullable();
            $table->text('observaciones_devolucion')->nullable();
            $table->enum('motivo_devolucion', ['normal', 'desgaste', 'perdida', 'danio', 'reemplazo'])->nullable();
            $table->string('estado_herramienta_asignacion')->nullable();
            $table->string('estado_herramienta_devolucion')->nullable();
            $table->integer('duracion_dias')->nullable();
            $table->timestamps();

            $table->index(['herramienta_id', 'fecha_asignacion']);
            $table->index(['tecnico_id', 'fecha_asignacion']);
            $table->index('fecha_devolucion');
            $table->index('motivo_devolucion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_herramientas');
    }
};
