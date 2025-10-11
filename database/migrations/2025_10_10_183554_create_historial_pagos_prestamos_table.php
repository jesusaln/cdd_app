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
        Schema::create('historial_pagos_prestamos', function (Blueprint $table) {
            $table->id();

            // Información del pago
            $table->foreignId('pago_prestamo_id')->constrained('pagos_prestamos')->onDelete('cascade');
            $table->foreignId('prestamo_id')->constrained('prestamos')->onDelete('cascade');
            $table->decimal('monto_pagado', 12, 2); // Monto que se pagó en esta transacción
            $table->date('fecha_pago'); // Fecha en que se realizó el pago
            $table->date('fecha_registro'); // Fecha en que se registró en el sistema

            // Información adicional
            $table->string('metodo_pago')->nullable(); // efectivo, transferencia, etc.
            $table->string('referencia')->nullable(); // número de referencia
            $table->text('notas')->nullable();
            $table->boolean('confirmado')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['pago_prestamo_id', 'fecha_pago']);
            $table->index(['prestamo_id', 'fecha_pago']);
            $table->index('fecha_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_pagos_prestamos');
    }
};
