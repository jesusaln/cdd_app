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
        // Mejorar tabla de asignaciones para mejor control
        Schema::table('asignacion_herramientas', function (Blueprint $table) {
            // Agregar campos para mejor control de asignaciones
            $table->enum('estado', ['pendiente', 'activa', 'completada', 'cancelada'])->default('pendiente')->after('activo');
            $table->timestamp('fecha_entrega_real')->nullable()->after('fecha_asignacion');
            $table->timestamp('fecha_recepcion_real')->nullable()->after('fecha_entrega_real');
            $table->text('notas_internas')->nullable()->after('observaciones_recepcion');
            $table->boolean('requiere_autorizacion')->default(false)->after('notas_internas');
            $table->foreignId('autorizado_por')->nullable()->constrained('users')->onDelete('set null')->after('requiere_autorizacion');
            $table->timestamp('fecha_autorizacion')->nullable()->after('autorizado_por');

            // Índices para mejor rendimiento
            $table->index(['estado', 'fecha_asignacion']);
            $table->index(['tecnico_id', 'estado']);
            $table->index(['herramienta_id', 'estado']);
        });

        // Crear tabla para asignaciones masivas (múltiples herramientas a un técnico)
        Schema::create('asignaciones_masivas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_asignacion')->unique(); // Código único para la asignación masiva
            $table->foreignId('tecnico_id')->constrained('tecnicos')->onDelete('cascade');
            $table->foreignId('asignado_por')->constrained('users')->onDelete('set null');
            $table->timestamp('fecha_asignacion');
            $table->timestamp('fecha_devolucion_programada')->nullable();
            $table->timestamp('fecha_devolucion_real')->nullable();
            $table->foreignId('recibido_por')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('estado', ['pendiente', 'activa', 'completada', 'cancelada'])->default('pendiente');
            $table->text('observaciones_asignacion')->nullable();
            $table->text('observaciones_devolucion')->nullable();
            $table->json('herramientas_ids'); // Array de IDs de herramientas
            $table->integer('total_herramientas')->default(0);
            $table->integer('herramientas_devueltas')->default(0);
            $table->string('proyecto_trabajo')->nullable(); // Para qué proyecto/trabajo se asignan
            $table->timestamps();

            $table->index(['tecnico_id', 'estado']);
            $table->index(['fecha_asignacion', 'estado']);
            $table->index('codigo_asignacion');
        });

        // Crear tabla para el detalle de asignaciones masivas
        Schema::create('detalle_asignaciones_masivas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_masiva_id')->constrained('asignaciones_masivas')->onDelete('cascade');
            $table->foreignId('herramienta_id')->constrained('herramientas')->onDelete('cascade');
            $table->enum('estado_individual', ['asignada', 'devuelta', 'perdida', 'dañada'])->default('asignada');
            $table->timestamp('fecha_asignacion_individual');
            $table->timestamp('fecha_devolucion_individual')->nullable();
            $table->text('observaciones_asignacion')->nullable();
            $table->text('observaciones_devolucion')->nullable();
            $table->string('estado_herramienta_entrega')->nullable();
            $table->string('estado_herramienta_devolucion')->nullable();
            $table->string('foto_estado_entrega')->nullable();
            $table->string('foto_estado_devolucion')->nullable();
            $table->timestamps();

            $table->index(['asignacion_masiva_id', 'estado_individual']);
            $table->index(['herramienta_id', 'estado_individual']);
        });

        // Mejorar tabla de historial para incluir referencia a asignaciones masivas
        Schema::table('historial_herramientas', function (Blueprint $table) {
            $table->foreignId('asignacion_masiva_id')->nullable()->constrained('asignaciones_masivas')->onDelete('set null')->after('tecnico_id');
            $table->string('codigo_asignacion')->nullable()->after('asignacion_masiva_id');
            $table->string('proyecto_trabajo')->nullable()->after('codigo_asignacion');
            $table->enum('tipo_asignacion', ['individual', 'masiva'])->default('individual')->after('proyecto_trabajo');

            $table->index(['asignacion_masiva_id', 'fecha_asignacion']);
            $table->index(['codigo_asignacion']);
            $table->index(['tipo_asignacion', 'fecha_asignacion']);
        });

        // Crear tabla para control de responsabilidades
        Schema::create('responsabilidades_herramientas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tecnico_id')->constrained('tecnicos')->onDelete('cascade');
            $table->json('herramientas_asignadas'); // Array de IDs de herramientas actualmente asignadas
            $table->integer('total_herramientas')->default(0);
            $table->decimal('valor_total_herramientas', 10, 2)->default(0);
            $table->timestamp('ultima_actualizacion');
            $table->boolean('tiene_herramientas_vencidas')->default(false);
            $table->integer('dias_promedio_uso')->default(0);
            $table->timestamps();

            $table->unique('tecnico_id');
            $table->index(['tecnico_id', 'total_herramientas']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responsabilidades_herramientas');
        Schema::dropIfExists('detalle_asignaciones_masivas');
        Schema::dropIfExists('asignaciones_masivas');

        Schema::table('historial_herramientas', function (Blueprint $table) {
            $table->dropForeign(['asignacion_masiva_id']);
            $table->dropColumn([
                'asignacion_masiva_id',
                'codigo_asignacion',
                'proyecto_trabajo',
                'tipo_asignacion'
            ]);
        });

        Schema::table('asignacion_herramientas', function (Blueprint $table) {
            $table->dropForeign(['autorizado_por']);
            $table->dropColumn([
                'estado',
                'fecha_entrega_real',
                'fecha_recepcion_real',
                'notas_internas',
                'requiere_autorizacion',
                'autorizado_por',
                'fecha_autorizacion'
            ]);
        });
    }
};
