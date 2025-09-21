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
        Schema::table('cobranzas', function (Blueprint $table) {
            $table->string('recibido_por')->nullable()->after('referencia_pago');
            $table->text('notas_pago')->nullable()->after('recibido_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cobranzas', function (Blueprint $table) {
            $table->dropColumn(['recibido_por', 'notas_pago']);
        });
    }
};
