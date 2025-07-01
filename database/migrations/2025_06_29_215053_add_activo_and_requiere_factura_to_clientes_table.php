<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('requiere_factura')->default('no')->after('pais');
            $table->boolean('activo')->default(true)->after('requiere_factura');
        });

        // Actualizar registros existentes
        DB::table('clientes')->update([
            'activo' => true,
            'requiere_factura' => DB::raw("CASE WHEN rfc = 'XAXX010101000' THEN 'no' ELSE 'si' END")
        ]);
    }

    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['requiere_factura', 'activo']);
        });
    }
};
