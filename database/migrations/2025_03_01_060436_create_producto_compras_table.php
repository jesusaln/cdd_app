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
        Schema::create('compra_producto', function (Blueprint $table) {
            $table->id(); // ID de la relación
            $table->foreignId('compra_id')->constrained()->onDelete('cascade'); // Relación con la compra
            $table->foreignId('producto_id')->constrained()->onDelete('cascade'); // Relación con el producto
            $table->integer('cantidad'); // Cantidad del producto en la compra
            $table->decimal('precio', 8, 2); // Precio del producto en la compra
            $table->timestamps(); // Columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_compras');
    }
};
