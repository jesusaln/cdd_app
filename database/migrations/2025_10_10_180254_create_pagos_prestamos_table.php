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
        Schema::create('pagos_prestamos', function (Blueprint $table) {
            $table->id();

            // Información del pago
            $table->foreignId('prestamo_id')->constrained('prestamos')->onDelete('cascade');
            $table->integer('numero_pago'); // Número del pago (1, 2, 3, etc.)
            $table->decimal('monto_programado', 12, 2); // Monto que debería pagarse
            $table->decimal('monto_pagado', 12, 2); // Monto que efectivamente se pagó
            $table->date('fecha_programada'); // Fecha en que debería pagarse
            $table->date('fecha_pago')->nullable(); // Fecha en que efectivamente se pagó
            $table->date('fecha_registro'); // Fecha en que se registró el pago

            // Estado del pago
            $table->enum('estado', ['pendiente', 'pagado', 'atrasado', 'parcial'])->default('pendiente');
            $table->integer('dias_atraso')->default(0);

            // Información adicional
            $table->text('notas')->nullable();
            $table->string('metodo_pago')->nullable(); // efectivo, transferencia, etc.
            $table->string('referencia')->nullable(); // número de referencia
            $table->boolean('confirmado')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['prestamo_id', 'estado']);
            $table->index(['fecha_programada', 'estado']);
            $table->index('fecha_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_prestamos');
    }
};
