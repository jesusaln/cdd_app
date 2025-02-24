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
            $table->decimal('total', 8, 2)->default(0); // Total del pedido
            $table->string('estado')->default('pendiente'); // Estado del pedido (pendiente, completado, cancelado, etc.)
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
        Schema::dropIfExists('pedidos');
    }
}
