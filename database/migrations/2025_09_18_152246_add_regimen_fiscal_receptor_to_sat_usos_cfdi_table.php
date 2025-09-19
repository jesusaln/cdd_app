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
        Schema::table('sat_usos_cfdi', function (Blueprint $table) {
            $table->text('regimen_fiscal_receptor')->nullable()
                  ->comment('Lista de claves de régimen fiscal compatibles separadas por coma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sat_usos_cfdi', function (Blueprint $table) {
            $table->dropColumn('regimen_fiscal_receptor');
        });
    }
};
