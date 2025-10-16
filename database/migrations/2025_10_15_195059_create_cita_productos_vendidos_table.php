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
        Schema::create('cita_productos_vendidos', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->onDelete('set null');

            // Información de la venta
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_venta', 12, 2);
            $table->decimal('descuento', 5, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->text('notas')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['cita_id', 'producto_id']);
            $table->index(['venta_id']);
            $table->index(['producto_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cita_productos_vendidos');
    }
};
