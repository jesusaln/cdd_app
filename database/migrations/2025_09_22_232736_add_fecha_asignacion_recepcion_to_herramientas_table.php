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
        // Agregar columnas solo si no existen (idempotente)
        if (!Schema::hasColumn('herramientas', 'fecha_asignacion') || !Schema::hasColumn('herramientas', 'fecha_recepcion')) {
            Schema::table('herramientas', function (Blueprint $table) {
                if (!Schema::hasColumn('herramientas', 'fecha_asignacion')) {
                    $table->timestamp('fecha_asignacion')->nullable()->after('dias_para_mantenimiento');
                }
                if (!Schema::hasColumn('herramientas', 'fecha_recepcion')) {
                    $table->timestamp('fecha_recepcion')->nullable()->after('fecha_asignacion');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('herramientas', function (Blueprint $table) {
            if (Schema::hasColumn('herramientas', 'fecha_asignacion')) {
                $table->dropColumn('fecha_asignacion');
            }
            if (Schema::hasColumn('herramientas', 'fecha_recepcion')) {
                $table->dropColumn('fecha_recepcion');
            }
        });
    }
};
