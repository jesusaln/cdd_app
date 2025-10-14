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
        Schema::table('empresa_configuracion', function (Blueprint $table) {
            // Eliminar campos antiguos que ya no se necesitan
            if (Schema::hasColumn('empresa_configuracion', 'direccion')) {
                $table->dropColumn('direccion');
            }
            if (Schema::hasColumn('empresa_configuracion', 'colonia')) {
                $table->dropColumn('colonia');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresa_configuracion', function (Blueprint $table) {
            // Restaurar campos antiguos si es necesario
            $table->text('direccion')->nullable()->after('razon_social');
            $table->string('colonia')->nullable()->after('codigo_postal');
        });
    }
};
