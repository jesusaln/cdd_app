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
            $table->id();

            // Relación con la cotización
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->onDelete('cascade');

            // Campos polimórficos para productos y servicios
            $table->unsignedBigInteger('cotizable_id');
            $table->string('cotizable_type');

            // Índice compuesto para búsquedas eficientes
            $table->index(['cotizable_id', 'cotizable_type']);

            // Datos del ítem en la cotización
            $table->integer('cantidad')->default(1);
            $table->decimal('precio', 10, 2);           // Precio unitario
            $table->decimal('descuento', 5, 2)->default(0); // Porcentaje de descuento (0-100)

            // Campos calculados (importantes para tu servicio)
            $table->decimal('subtotal', 10, 2);         // cantidad * precio
            $table->decimal('descuento_monto', 10, 2);  // subtotal * (descuento / 100)

            // Timestamps
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
        Schema::dropIfExists('cotizacion_producto');
    }
}
