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
        Schema::table('entregas_dinero', function (Blueprint $table) {
            // Referencia al origen (cobranza o venta)
            $table->string('tipo_origen')->nullable()->after('notas'); // 'cobranza' o 'venta'
            $table->unsignedBigInteger('id_origen')->nullable()->after('tipo_origen'); // ID del registro origen

            $table->index(['tipo_origen', 'id_origen']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entregas_dinero', function (Blueprint $table) {
            $table->dropIndex(['tipo_origen', 'id_origen']);
            $table->dropColumn(['tipo_origen', 'id_origen']);
        });
    }
};
