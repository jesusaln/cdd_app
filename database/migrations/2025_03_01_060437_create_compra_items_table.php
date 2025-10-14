<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompraItemsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('compra_items', function (Blueprint $table) {
            $table->id();

            // Relación con la compra
            $table->foreignId('compra_id')->constrained('compras')->onDelete('cascade');

            // Relación polimórfica: productos, servicios, etc.
            $table->unsignedBigInteger('comprable_id');
            $table->string('comprable_type');

            // Índice compuesto para búsquedas eficientes
            $table->index(['comprable_id', 'comprable_type']);

            // Datos del ítem
            $table->integer('cantidad')->default(1);
            $table->decimal('precio', 10, 2);
            $table->decimal('descuento', 5, 2)->default(0);

            // Campos calculados
            $table->decimal('subtotal', 10, 2);
            $table->decimal('descuento_monto', 10, 2);

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('compra_items');
    }
}
