<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->integer('km_anticipacion_alerta')->default(500)->after('frecuencia_recordatorio_dias');
            $table->index('km_anticipacion_alerta');
        });
    }

    public function down(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropIndex(['km_anticipacion_alerta']);
            $table->dropColumn('km_anticipacion_alerta');
        });
    }
};

