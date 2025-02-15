<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCotizacionProductoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cotizacion_producto', function (Blueprint $table) {
            $table->id(); // ID de la relación
            $table->foreignId('cotizacion_id')->constrained()->onDelete('cascade'); // Relación con la cotización
            $table->foreignId('producto_id')->constrained()->onDelete('cascade'); // Relación con el producto
            $table->decimal('precio', 8, 2); // Precio del producto en la cotización
            $table->integer('cantidad')->default(1); // Cantidad del producto en la cotización
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
        Schema::dropIfExists('cotizacion_producto');
    }
}
