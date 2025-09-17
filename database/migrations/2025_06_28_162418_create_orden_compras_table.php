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
        Schema::create('orden_compras', function (Blueprint $table) {
            $table->id();

            // Relación
            $table->foreignId('proveedor_id')
                ->constrained('proveedores')
                ->cascadeOnDelete();

            // Campos básicos de información
            $table->string('numero_orden')->nullable()->unique();        // único aunque sea nullable
            $table->date('fecha_orden')->nullable();
            $table->date('fecha_entrega_esperada')->nullable();

            // En SQLite enum se maneja como TEXT; en MySQL sí es ENUM; ok para ambos.
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');

            // Entrega y pago
            $table->text('direccion_entrega')->nullable();
            $table->enum('terminos_pago', ['contado', '15_dias', '30_dias', '45_dias', '60_dias', '90_dias'])->default('30_dias');
            $table->enum('metodo_pago', ['transferencia', 'cheque', 'efectivo', 'tarjeta'])->default('transferencia');

            // Financieros
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento_items', 10, 2)->default(0);
            $table->decimal('descuento_general', 10, 2)->default(0);
            $table->decimal('iva', 10, 2)->default(0);

            // Totales/estado/fechas
            $table->decimal('total', 10, 2)->default(0); // si lo calculas, igual déjalo para almacenar cierre
            $table->string('estado')->default('pendiente');
            $table->timestamp('fecha_recepcion')->nullable();

            // Observaciones
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_compras');
    }
};
