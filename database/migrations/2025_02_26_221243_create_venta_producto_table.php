<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('venta_producto', function (Blueprint $table) {
            $table->id(); // ID de la relación
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade'); // Relación con la venta

            // Campos polimórficos
            $table->unsignedBigInteger('vendible_id');
            $table->string('vendible_type');

            $table->integer('cantidad'); // Cantidad del producto o servicio en la venta
            $table->decimal('precio', 8, 2); // Precio del producto o servicio en la venta
            $table->timestamps(); // Columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venta_producto');
    }
};
