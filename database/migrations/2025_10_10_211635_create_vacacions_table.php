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
        Schema::create('vacacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->integer('dias_solicitados');
            $table->integer('dias_pendientes')->default(0);
            $table->integer('dias_aprobados')->default(0);
            $table->integer('dias_rechazados')->default(0);
            $table->text('motivo')->nullable();
            $table->string('estado')->default('pendiente'); // pendiente, aprobada, rechazada, cancelada
            $table->text('observaciones')->nullable();
            $table->foreignId('aprobador_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('fecha_aprobacion')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'estado']);
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacacions');
    }
};
