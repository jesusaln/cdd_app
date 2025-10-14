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
        Schema::table('entregas_dinero', function (Blueprint $table) {
            $table->boolean('entregado_responsable')->default(false)->after('estado');
            $table->datetime('fecha_entregado_responsable')->nullable()->after('entregado_responsable');
            $table->string('responsable_organizacion')->nullable()->after('fecha_entregado_responsable');
            $table->text('notas_entrega_responsable')->nullable()->after('responsable_organizacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entregas_dinero', function (Blueprint $table) {
            $table->dropColumn([
                'entregado_responsable',
                'fecha_entregado_responsable',
                'responsable_organizacion',
                'notas_entrega_responsable'
            ]);
        });
    }
};
