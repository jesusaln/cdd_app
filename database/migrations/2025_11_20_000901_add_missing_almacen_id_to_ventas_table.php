<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Solo crear la columna si falta (para entornos con datos previos)
            if (!Schema::hasColumn('ventas', 'almacen_id')) {
                $table->foreignId('almacen_id')
                    ->nullable()
                    ->constrained('almacenes')
                    ->onDelete('cascade')
                    ->after('cliente_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (Schema::hasColumn('ventas', 'almacen_id')) {
                $table->dropForeign(['almacen_id']);
                $table->dropColumn('almacen_id');
            }
        });
    }
};

