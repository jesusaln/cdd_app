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
        Schema::table('herramientas', function (Blueprint $table) {
            $table->timestamp('fecha_asignacion')->nullable()->after('dias_para_mantenimiento');
            $table->timestamp('fecha_recepcion')->nullable()->after('fecha_asignacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('herramientas', function (Blueprint $table) {
            $table->dropColumn(['fecha_asignacion', 'fecha_recepcion']);
        });
    }
};
