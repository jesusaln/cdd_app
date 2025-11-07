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
        Schema::table('ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas', 'almacen_id')) {
                // Mantener nullable para no fallar en tablas con datos existentes
                $table->foreignId('almacen_id')
                    ->nullable()
                    ->constrained('almacenes')
                    ->onDelete('cascade')
                    ->after('cliente_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
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

