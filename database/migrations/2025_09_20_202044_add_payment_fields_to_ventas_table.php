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
            $table->boolean('pagado')->default(false)->after('total');
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'cheque', 'tarjeta', 'otros'])->nullable()->after('pagado');
            $table->date('fecha_pago')->nullable()->after('metodo_pago');
            $table->text('notas_pago')->nullable()->after('fecha_pago');
            $table->unsignedBigInteger('pagado_por')->nullable()->after('notas_pago');
            $table->foreign('pagado_por')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['pagado_por']);
            $table->dropColumn(['pagado', 'metodo_pago', 'fecha_pago', 'notas_pago', 'pagado_por']);
        });
    }
};
