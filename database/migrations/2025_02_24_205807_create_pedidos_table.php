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
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade'); // Relación con el cliente
            $table->unsignedBigInteger('cotizacion_id')->nullable(); // Relación opcional con cotización
            $table->string('numero_pedido')->unique(); // Número único del pedido
            $table->decimal('subtotal', 10, 2)->default(0); // Subtotal del pedido
            $table->decimal('descuento_general', 10, 2)->default(0); // Descuento general
            $table->decimal('iva', 10, 2)->default(0); // IVA
            $table->timestamp('fecha')->nullable(); // Fecha del pedido
            $table->decimal('total', 10, 2)->default(0); // Total del pedido
            $table->string('estado')->default('borrador'); // Estado del pedido (borrador, pendiente, completado, cancelado, etc.)
            $table->text('notas')->nullable()->after('estado'); // Notas adicionales
            $table->timestamps(); // Columnas created_at y updated_at

            // Índices para mejor rendimiento
            $table->index('numero_pedido');
            $table->index('estado');
            $table->index('fecha');

            // Clave foránea opcional para cotizaciones (descomenta si tienes tabla cotizaciones)
            // $table->foreign('cotizacion_id')->references('id')->on('cotizaciones')->onDelete('set null');
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
