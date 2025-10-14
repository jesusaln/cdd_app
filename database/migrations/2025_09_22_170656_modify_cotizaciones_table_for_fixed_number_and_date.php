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
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropUnique(['numero_cotizacion']);
            $table->string('numero_cotizacion', 30)->change();
            $table->date('fecha_cotizacion')->nullable()->after('numero_cotizacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->string('numero_cotizacion', 30)->unique()->change();
            $table->dropColumn('fecha_cotizacion');
        });
    }
};
