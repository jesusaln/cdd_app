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
        Schema::table('equipos', function (Blueprint $table) {
            // Componentes del kit con números de serie
            $table->string('computadora_numero_serie')->nullable();
            $table->string('bascula_numero_serie')->nullable();
            $table->string('lector_codigo_barras_numero_serie')->nullable();
            $table->string('cajon_dinero_numero_serie')->nullable();
            $table->string('sistema_numero_serie')->nullable();
            $table->string('impresora_ticket_numero_serie')->nullable();
            $table->string('otro_componente')->nullable();
            $table->string('otro_numero_serie')->nullable();

            // Foto del kit completo
            $table->string('foto_kit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn([
                'computadora_numero_serie',
                'bascula_numero_serie',
                'lector_codigo_barras_numero_serie',
                'cajon_dinero_numero_serie',
                'sistema_numero_serie',
                'impresora_ticket_numero_serie',
                'otro_componente',
                'otro_numero_serie',
                'foto_kit'
            ]);
        });
    }
};
