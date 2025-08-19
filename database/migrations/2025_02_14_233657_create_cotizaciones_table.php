<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCotizacionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id(); // ID de la cotización

            // Relación con el cliente
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');

            // Campos de cálculo
            $table->decimal('subtotal', 10, 2)->nullable();       // Suma de ítems
            $table->decimal('descuento_general', 10, 2)->default(0); // Monto del descuento general
            $table->decimal('iva', 10, 2)->nullable();           // IVA calculado
            $table->decimal('total', 10, 2)->default(0);         // Total final

            // Estado (compatible con enum EstadoCotizacion)
            $table->string('estado')->default('pendiente');

            // Notas adicionales
            $table->text('notas')->nullable();

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
        Schema::dropIfExists('cotizaciones');
    }
}
