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
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();

            // Información del préstamo
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->decimal('monto_prestado', 12, 2); // Cantidad prestada
            $table->decimal('tasa_interes', 5, 2); // Tasa de interés (0.00 a 100.00)
            $table->integer('numero_pagos'); // Número total de pagos
            $table->enum('frecuencia_pago', ['semanal', 'quincenal', 'mensual']); // Frecuencia de pagos
            $table->date('fecha_inicio'); // Fecha de inicio del préstamo
            $table->date('fecha_primer_pago')->nullable(); // Fecha del primer pago

            // Cálculos financieros
            $table->decimal('monto_interes_total', 12, 2); // Interés total a pagar
            $table->decimal('monto_total_pagar', 12, 2); // Total incluyendo interés
            $table->decimal('pago_periodico', 12, 2); // Cantidad por pago

            // Estado del préstamo
            $table->enum('estado', ['activo', 'completado', 'cancelado'])->default('activo');
            $table->integer('pagos_realizados')->default(0);
            $table->integer('pagos_pendientes')->default(0);
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->decimal('monto_pendiente', 12, 2)->default(0);

            // Información adicional
            $table->text('descripcion')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['cliente_id', 'estado']);
            $table->index(['estado', 'activo']);
            $table->index('fecha_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
