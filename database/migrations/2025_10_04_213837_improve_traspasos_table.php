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
        Schema::table('traspasos', function (Blueprint $table) {
            // Agregar campos para mejorar el sistema de traspasos
            $table->enum('estado', ['pendiente', 'en_transito', 'completado', 'cancelado'])->default('completado')->after('cantidad');
            $table->foreignId('usuario_autoriza')->constrained('users')->onDelete('cascade')->after('estado');
            $table->foreignId('usuario_envia')->nullable()->constrained('users')->onDelete('set null')->after('usuario_autoriza');
            $table->foreignId('usuario_recibe')->nullable()->constrained('users')->onDelete('set null')->after('usuario_envia');
            $table->timestamp('fecha_envio')->nullable()->after('usuario_recibe');
            $table->timestamp('fecha_recepcion')->nullable()->after('fecha_envio');
            $table->text('observaciones')->nullable()->after('fecha_recepcion');
            $table->string('referencia', 100)->nullable()->after('observaciones');
            $table->decimal('costo_transporte', 10, 2)->nullable()->after('referencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('traspasos', function (Blueprint $table) {
            $table->dropForeign(['usuario_autoriza']);
            $table->dropForeign(['usuario_envia']);
            $table->dropForeign(['usuario_recibe']);
            $table->dropColumn([
                'estado',
                'usuario_autoriza',
                'usuario_envia',
                'usuario_recibe',
                'fecha_envio',
                'fecha_recepcion',
                'observaciones',
                'referencia',
                'costo_transporte'
            ]);
        });
    }
};
