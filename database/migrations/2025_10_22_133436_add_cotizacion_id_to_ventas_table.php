<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar columna si no existe y asegurar la FK a 'cotizaciones'
        if (!Schema::hasColumn('ventas', 'cotizacion_id')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->foreignId('cotizacion_id')->nullable();
            });
        }

        // Agregar la clave foránea si no existe aún
        $fkName = 'ventas_cotizacion_id_foreign';
        $exists = collect(DB::select("SELECT conname FROM pg_constraint WHERE conname = ?", [$fkName]))->isNotEmpty();
        if (!$exists) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->foreign('cotizacion_id')->references('id')->on('cotizaciones')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (Schema::hasColumn('ventas', 'cotizacion_id')) {
                // Intentar soltar la FK si existe (nombre convencional)
                try {
                    $table->dropForeign(['cotizacion_id']);
                } catch (\Throwable $e) {
                    // Ignorar si no existe
                }
                $table->dropColumn('cotizacion_id');
            }
        });
    }
};

