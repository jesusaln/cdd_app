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
        Schema::table('citas', function (Blueprint $table) {
            // Agregar soft deletes solo si no existe
            if (!Schema::hasColumn('citas', 'deleted_at')) {
                $table->softDeletes();
            }

            // Agregar índices para mejorar rendimiento
            $table->index(['tecnico_id', 'fecha_hora']);
            $table->index(['cliente_id', 'fecha_hora']);
            $table->index('estado');
            $table->index('fecha_hora');
            $table->index('prioridad');
            if (Schema::hasColumn('citas', 'deleted_at')) {
                $table->index('deleted_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            // Eliminar índices
            $table->dropIndex(['tecnico_id', 'fecha_hora']);
            $table->dropIndex(['cliente_id', 'fecha_hora']);
            $table->dropIndex(['estado']);
            $table->dropIndex(['fecha_hora']);
            $table->dropIndex(['prioridad']);
            if (Schema::hasColumn('citas', 'deleted_at')) {
                $table->dropIndex(['deleted_at']);
            }

            // Eliminar soft deletes solo si existe
            if (Schema::hasColumn('citas', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
