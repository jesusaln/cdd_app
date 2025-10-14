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
        Schema::create('entregas_dinero', function (Blueprint $table) {
            $table->id();

            // Usuario que trae el dinero
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Información de la entrega
            $table->date('fecha_entrega');
            $table->decimal('monto_efectivo', 10, 2)->default(0);
            $table->decimal('monto_cheques', 10, 2)->default(0);
            $table->decimal('monto_tarjetas', 10, 2)->default(0);
            $table->decimal('total', 10, 2);

            // Estado de la entrega
            $table->enum('estado', [
                'pendiente',    // El usuario declaró que trae dinero
                'recibido',     // El administrador recibió el dinero
                'cancelado'     // Cancelado
            ])->default('pendiente');

            // Notas del usuario al declarar
            $table->text('notas')->nullable();

            // Información de recepción
            $table->foreignId('recibido_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('fecha_recibido')->nullable();
            $table->text('notas_recibido')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['user_id', 'estado']);
            $table->index(['fecha_entrega', 'estado']);
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregas_dinero');
    }
};
