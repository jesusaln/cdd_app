<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');

            // Relación polimórfica: productos, servicios, etc.
            $table->unsignedBigInteger('pedible_id');
            $table->string('pedible_type'); // Ej: App\Models\Producto, App\Models\Servicio

            $table->decimal('precio', 8, 2);
            $table->integer('cantidad')->default(1);
            $table->decimal('descuento', 8, 2)->default(0); // Opcional: por si hay descuento por ítem
            $table->decimal('subtotal', 8, 2); // opcional: puedes calcularlo, pero útil guardarlo
            $table->decimal('descuento_monto', 8, 2)->default(0);
            $table->timestamps();
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
