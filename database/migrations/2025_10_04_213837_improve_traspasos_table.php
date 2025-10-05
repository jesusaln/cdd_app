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
            if (!Schema::hasColumn('traspasos', 'estado')) {
                $table->enum('estado', ['pendiente', 'en_transito', 'completado', 'cancelado'])->default('completado')->after('cantidad');
            }
            if (!Schema::hasColumn('traspasos', 'usuario_autoriza')) {
                $table->foreignId('usuario_autoriza')->nullable()->constrained('users')->onDelete('set null')->after('estado');
            }
            if (!Schema::hasColumn('traspasos', 'usuario_envia')) {
                $table->foreignId('usuario_envia')->nullable()->constrained('users')->onDelete('set null')->after('usuario_autoriza');
            }
            if (!Schema::hasColumn('traspasos', 'usuario_recibe')) {
                $table->foreignId('usuario_recibe')->nullable()->constrained('users')->onDelete('set null')->after('usuario_envia');
            }
            if (!Schema::hasColumn('traspasos', 'fecha_envio')) {
                $table->timestamp('fecha_envio')->nullable()->after('usuario_recibe');
            }
            if (!Schema::hasColumn('traspasos', 'fecha_recepcion')) {
                $table->timestamp('fecha_recepcion')->nullable()->after('fecha_envio');
            }
            if (!Schema::hasColumn('traspasos', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('fecha_recepcion');
            }
            if (!Schema::hasColumn('traspasos', 'referencia')) {
                $table->string('referencia', 100)->nullable()->after('observaciones');
            }
            if (!Schema::hasColumn('traspasos', 'costo_transporte')) {
                $table->decimal('costo_transporte', 10, 2)->nullable()->after('referencia');
            }
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
