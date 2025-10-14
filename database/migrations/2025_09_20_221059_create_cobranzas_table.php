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
        Schema::create('cobranzas', function (Blueprint $table) {
            $table->id();

            // Relación con renta
            $table->foreignId('renta_id')->constrained('rentas')->onDelete('cascade');

            // Información del cobro
            $table->date('fecha_cobro'); // Fecha en que se debe cobrar
            $table->decimal('monto_cobrado', 10, 2); // Monto a cobrar
            $table->string('concepto'); // mensualidad, deposito_garantia, etc.

            // Información del pago
            $table->enum('estado', [
                'pendiente',    // No pagado aún
                'pagado',       // Pagado completamente
                'parcial',      // Pagado parcialmente
                'vencido',      // Fecha de cobro pasada sin pagar
                'cancelado'     // Cobro cancelado
            ])->default('pendiente');

            $table->date('fecha_pago')->nullable(); // Fecha en que se pagó
            $table->decimal('monto_pagado', 10, 2)->default(0); // Monto realmente pagado
            $table->string('metodo_pago')->nullable(); // efectivo, transferencia, tarjeta, etc.
            $table->string('referencia_pago')->nullable(); // Número de recibo, transferencia, etc.

            // Información adicional
            $table->text('notas')->nullable(); // Notas sobre el cobro
            $table->string('responsable_cobro')->nullable(); // Quién realizó el cobro

            $table->timestamps();

            // Índices para optimización
            $table->index(['renta_id', 'estado']);
            $table->index(['fecha_cobro', 'estado']);
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobranzas');
    }
};
