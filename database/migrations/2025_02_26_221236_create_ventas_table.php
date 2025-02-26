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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id(); // ID de la venta
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade'); // Relación con el cliente
            $table->decimal('total', 8, 2)->default(0); // Total de la venta
            $table->string('estado')->default('pendiente'); // Estado de la venta (pendiente, completada, cancelada, etc.)
            $table->timestamps(); // Columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
