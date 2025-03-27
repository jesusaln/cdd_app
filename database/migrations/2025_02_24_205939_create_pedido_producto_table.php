<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoProductoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_producto', function (Blueprint $table) {
            $table->id(); // ID de la relación
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade'); // Relación con el pedido

            // Campos polimórficos
            $table->unsignedBigInteger('pedible_id');
            $table->string('pedible_type');

            $table->decimal('precio', 8, 2); // Precio del producto o servicio en el pedido
            $table->integer('cantidad')->default(1); // Cantidad del producto o servicio en el pedido
            $table->timestamps(); // Columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pedido_producto');
    }
}
