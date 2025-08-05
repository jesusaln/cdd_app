<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCotizacionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cotizacion_items', function (Blueprint $table) {
            $table->id();

            // Relación con la cotización
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->onDelete('cascade');

            // Relación polimórfica: productos, servicios, etc.
            $table->unsignedBigInteger('cotizable_id');
            $table->string('cotizable_type');

            // Índice compuesto para búsquedas eficientes
            $table->index(['cotizable_id', 'cotizable_type']);

            // Datos del ítem
            $table->integer('cantidad')->default(1);
            $table->decimal('precio', 10, 2);           // Precio unitario
            $table->decimal('descuento', 5, 2)->default(0); // Porcentaje de descuento

            // Campos calculados
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
        Schema::dropIfExists('cotizacion_items');
    }
}
