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
        Schema::table('cuentas_por_pagar', function (Blueprint $table) {
            $table->boolean('pagado')->default(false)->after('estado');
            $table->string('metodo_pago')->nullable()->after('pagado');
            $table->datetime('fecha_pago')->nullable()->after('metodo_pago');
            $table->unsignedBigInteger('pagado_por')->nullable()->after('fecha_pago');
            $table->text('notas_pago')->nullable()->after('pagado_por');

            $table->foreign('pagado_por')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuentas_por_pagar', function (Blueprint $table) {
            $table->dropForeign(['pagado_por']);
            $table->dropColumn([
                'pagado',
                'metodo_pago',
                'fecha_pago',
                'pagado_por',
                'notas_pago'
            ]);
        });
    }
};
