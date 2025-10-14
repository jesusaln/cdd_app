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
            $table->boolean('email_enviado')->default(false)->after('estado');
            $table->timestamp('email_enviado_fecha')->nullable()->after('email_enviado');
            $table->unsignedBigInteger('email_enviado_por')->nullable()->after('email_enviado_fecha');
            $table->foreign('email_enviado_por')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropForeign(['email_enviado_por']);
            $table->dropColumn([
                'email_enviado',
                'email_enviado_fecha',
                'email_enviado_por'
            ]);
        });
    }
};
