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
        Schema::table('mantenimientos', function (Blueprint $table) {
            // Sistema de prioridades
            $table->enum('prioridad', ['baja', 'media', 'alta', 'critica'])->default('media')->after('estado');

            // Sistema de alertas
            $table->boolean('alerta_enviada')->default(false)->after('prioridad');
            $table->timestamp('alerta_enviada_at')->nullable()->after('alerta_enviada');
            $table->integer('dias_anticipacion_alerta')->default(30)->after('alerta_enviada_at');

            // Información adicional para alertas
            $table->text('observaciones_alerta')->nullable()->after('dias_anticipacion_alerta');
            $table->boolean('requiere_aprobacion')->default(false)->after('observaciones_alerta');
            $table->string('tipo_alerta')->default('automatica')->after('requiere_aprobacion');

            // Recordatorios adicionales
            $table->json('recordatorios_enviados')->nullable()->after('tipo_alerta');
            $table->integer('frecuencia_recordatorio_dias')->default(7)->after('recordatorios_enviados');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropColumn([
                'prioridad',
                'alerta_enviada',
                'alerta_enviada_at',
                'dias_anticipacion_alerta',
                'observaciones_alerta',
                'requiere_aprobacion',
                'tipo_alerta',
                'recordatorios_enviados',
                'frecuencia_recordatorio_dias'
            ]);
        });
    }
};
