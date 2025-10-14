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
        Schema::table('clientes', function (Blueprint $table) {
            // Campos CFDI 4.0 adicionales para el receptor
            $table->char('domicilio_fiscal_cp', 5)->nullable()->after('codigo_postal')
                  ->comment('Código postal del domicilio fiscal del cliente (CFDI 4.0)');
            $table->char('residencia_fiscal', 3)->nullable()->after('domicilio_fiscal_cp')
                  ->comment('Clave de país de residencia fiscal para extranjeros (c_Pais)');
            $table->string('num_reg_id_trib', 40)->nullable()->after('residencia_fiscal')
                  ->comment('Número de registro fiscal extranjero');

            // Índices para búsquedas eficientes
            $table->index(['domicilio_fiscal_cp']);
            $table->index(['residencia_fiscal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex(['domicilio_fiscal_cp']);
            $table->dropIndex(['residencia_fiscal']);

            $table->dropColumn([
                'domicilio_fiscal_cp',
                'residencia_fiscal',
                'num_reg_id_trib',
            ]);
        });
    }
};
