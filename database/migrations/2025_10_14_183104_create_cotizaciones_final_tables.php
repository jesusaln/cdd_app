<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Nueva migración consolidada para tablas de cotizaciones
     */
    public function up(): void
    {
        // Crear tabla principal de cotizaciones
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();

            // Relación con el cliente
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');

            // Auditoría de usuarios
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            // Identificador legible
            $table->string('numero_cotizacion', 30);
            $table->date('fecha_cotizacion')->nullable();

            // Campos de cálculo
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('descuento_general', 10, 2)->default(0);
            $table->decimal('descuento_items', 10, 2)->default(0);
            $table->decimal('iva', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->default(0);

            // Estado y notas
            $table->string('estado')->default('pendiente');
            $table->text('notas')->nullable();

            // Email tracking
            $table->boolean('email_enviado')->default(false);
            $table->timestamp('email_enviado_fecha')->nullable();
            $table->foreignId('email_enviado_por')->nullable()->constrained('users')->nullOnDelete();

            // Timestamps y soft deletes
            $table->timestamps();
            $table->softDeletes();

            // Índices de rendimiento
            $table->index('numero_cotizacion');
            $table->index('estado');
            $table->index('fecha_cotizacion');
            $table->index(['cliente_id', 'estado']);
            $table->index(['email_enviado', 'estado']);
        });

        // Crear tabla de items de cotización
        Schema::create('cotizacion_items', function (Blueprint $table) {
            $table->id();

            // Relación con la cotización
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->onDelete('cascade');

            // Relación polimórfica con productos/servicios
            $table->unsignedBigInteger('cotizable_id');
            $table->string('cotizable_type');

            // Datos del ítem
            $table->integer('cantidad')->default(1);
            $table->decimal('precio', 10, 2);
            $table->decimal('descuento', 5, 2)->default(0);

            // Campos calculados
            $table->decimal('subtotal', 10, 2);
            $table->decimal('descuento_monto', 10, 2);

            $table->timestamps();

            // Índices de rendimiento
            $table->index(['cotizable_id', 'cotizable_type']);
            $table->index(['cotizacion_id', 'cantidad']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizacion_items');
        Schema::dropIfExists('cotizaciones');
    }
};
