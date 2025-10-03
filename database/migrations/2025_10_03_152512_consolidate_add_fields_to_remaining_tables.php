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
        // Add fields to mantenimientos
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->enum('prioridad', ['baja', 'media', 'alta', 'critica'])->default('media')->after('estado');
            $table->boolean('alerta_enviada')->default(false)->after('prioridad');
            $table->timestamp('alerta_enviada_at')->nullable()->after('alerta_enviada');
            $table->integer('dias_anticipacion_alerta')->default(30)->after('alerta_enviada_at');
            $table->text('observaciones_alerta')->nullable()->after('dias_anticipacion_alerta');
            $table->boolean('requiere_aprobacion')->default(false)->after('observaciones_alerta');
            $table->string('tipo_alerta')->default('automatica')->after('requiere_aprobacion');
            $table->json('recordatorios_enviados')->nullable()->after('tipo_alerta');
            $table->integer('frecuencia_recordatorio_dias')->default(7)->after('recordatorios_enviados');
        });

        // Add fields to equipos
        Schema::table('equipos', function (Blueprint $table) {
            $table->string('imagen')->nullable()->after('especificaciones');
        });

        // Add fields to inventario_movimientos
        Schema::table('inventario_movimientos', function (Blueprint $table) {
            $table->integer('stock_anterior')->nullable()->after('cantidad');
            $table->integer('stock_posterior')->nullable()->after('stock_anterior');
            $table->string('referencia_type')->nullable()->after('motivo');
            $table->unsignedBigInteger('referencia_id')->nullable()->after('referencia_type');
            $table->json('detalles')->nullable()->after('referencia_id');
            $table->index(['referencia_type', 'referencia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove fields from mantenimientos
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

        // Remove fields from equipos
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn('imagen');
        });

        // Remove fields from inventario_movimientos
        Schema::table('inventario_movimientos', function (Blueprint $table) {
            $table->dropColumn([
                'stock_anterior',
                'stock_posterior',
                'referencia_type',
                'referencia_id',
                'detalles'
            ]);
        });
    }
};
