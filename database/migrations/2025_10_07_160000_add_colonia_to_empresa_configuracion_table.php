<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa_configuracion', function (Blueprint $table) {
            if (!Schema::hasColumn('empresa_configuracion', 'colonia')) {
                $table->string('colonia', 255)->nullable()->after('codigo_postal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('empresa_configuracion', function (Blueprint $table) {
            if (Schema::hasColumn('empresa_configuracion', 'colonia')) {
                $table->dropColumn('colonia');
            }
        });
    }
};

