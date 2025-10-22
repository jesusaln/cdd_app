<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Eliminar el campo impuesto de la tabla productos ya que el IVA es global
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('impuesto');
        });
    }

    /**
     * Reverse the migrations.
     * Recrear el campo impuesto en caso de rollback
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('impuesto', 5, 2)->default(0)->after('comision_vendedor');
        });
    }
};
