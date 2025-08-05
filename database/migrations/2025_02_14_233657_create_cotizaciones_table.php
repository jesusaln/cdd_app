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

            // Campos de cálculo (equivalentes a los de Pedido)
            $table->decimal('subtotal', 10, 2)->nullable()->after('cliente_id'); // Suma de ítems
            $table->decimal('descuento_general', 10, 2)->default(0)->after('subtotal'); // Monto del descuento general
            $table->decimal('iva', 10, 2)->nullable()->after('descuento_general'); // IVA calculado
            $table->decimal('total', 10, 2)->default(0)->after('iva'); // Total final

            // Estado (compatible con enum EstadoCotizacion)
            $table->string('estado')->default('pendiente')->after('total');

            // Notas adicionales
            $table->text('notas')->nullable()->after('estado');

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
