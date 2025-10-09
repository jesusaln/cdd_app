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
        Schema::create('recordatorios_cobranza', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cuenta_por_cobrar_id');
            $table->foreign('cuenta_por_cobrar_id')->references('id')->on('cuentas_por_cobrar')->onDelete('cascade');
            $table->enum('tipo_recordatorio', ['vencimiento', 'dia_siguiente', 'cada_3_dias']);
            $table->datetime('fecha_envio');
            $table->datetime('fecha_proximo_recordatorio')->nullable();
            $table->boolean('enviado')->default(false);
            $table->integer('numero_intento')->default(1);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['enviado', 'fecha_envio']);
            $table->index(['tipo_recordatorio', 'fecha_envio']);
            $table->index('cuenta_por_cobrar_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recordatorios_cobranza');
    }
};
