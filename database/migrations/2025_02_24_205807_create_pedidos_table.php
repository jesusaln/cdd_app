<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id(); // ID del pedido

            // Relaciones
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->onUpdate('cascade')
                ->onDelete('cascade'); // Relación con el cliente

            $table->foreignId('cotizacion_id')
                ->nullable()
                ->constrained('cotizaciones')
                ->onUpdate('cascade')
                ->onDelete('set null'); // Relación opcional con cotización

            // Datos del pedido
            $table->string('numero_pedido')->unique(); // Número único del pedido
            $table->decimal('subtotal', 10, 2)->default(0); // Subtotal del pedido
            $table->decimal('descuento_general', 10, 2)->default(0); // Descuento general
            $table->decimal('iva', 10, 2)->default(0); // IVA
            $table->decimal('total', 10, 2)->default(0); // Total del pedido
            $table->timestamp('fecha')->nullable(); // Fecha del pedido
            $table->string('estado')->default('borrador'); // Estado del pedido
            $table->text('notas')->nullable(); // Notas adicionales

            // Timestamps
            $table->timestamps(); // Columnas created_at y updated_at

            // Índices para mejor rendimiento
            $table->index('numero_pedido');
            $table->index('estado');
            $table->index('fecha');
            $table->index(['cliente_id', 'estado']); // Índice compuesto para consultas frecuentes
            $table->index(['cotizacion_id', 'estado']); // Índice compuesto para relación con cotizaciones
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
}
