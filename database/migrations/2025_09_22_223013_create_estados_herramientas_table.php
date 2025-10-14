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
        Schema::create('estados_herramientas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('herramienta_id')->constrained('herramientas')->onDelete('cascade');
            $table->enum('condicion_general', ['excelente', 'buena', 'regular', 'mala', 'critica']);
            $table->decimal('porcentaje_desgaste', 5, 2)->default(0);
            $table->boolean('necesita_mantenimiento')->default(false);
            $table->boolean('necesita_reemplazo')->default(false);
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_inspeccion');
            $table->foreignId('inspeccionado_por')->constrained('users')->onDelete('set null');
            $table->string('foto_estado')->nullable();
            $table->enum('prioridad_mantenimiento', ['baja', 'media', 'alta', 'urgente'])->default('baja');
            $table->timestamps();

            $table->index(['herramienta_id', 'fecha_inspeccion']);
            $table->index('fecha_inspeccion');
            $table->index('prioridad_mantenimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estados_herramientas');
    }
};
