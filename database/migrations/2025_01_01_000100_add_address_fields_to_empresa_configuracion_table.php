<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa_configuracion', function (Blueprint $table) {
            if (!Schema::hasColumn('empresa_configuracion', 'calle')) {
                $table->string('calle', 255)->nullable()->after('razon_social');
            }
            if (!Schema::hasColumn('empresa_configuracion', 'numero_exterior')) {
                $table->string('numero_exterior', 20)->nullable()->after('calle');
            }
            if (!Schema::hasColumn('empresa_configuracion', 'numero_interior')) {
                $table->string('numero_interior', 20)->nullable()->after('numero_exterior');
            }
        });
    }

    public function down(): void
    {
        Schema::table('empresa_configuracion', function (Blueprint $table) {
            if (Schema::hasColumn('empresa_configuracion', 'numero_interior')) {
                $table->dropColumn('numero_interior');
            }
            if (Schema::hasColumn('empresa_configuracion', 'numero_exterior')) {
                $table->dropColumn('numero_exterior');
            }
            if (Schema::hasColumn('empresa_configuracion', 'calle')) {
                $table->dropColumn('calle');
            }
        });
    }
};

