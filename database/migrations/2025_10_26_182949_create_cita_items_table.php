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
        Schema::create('cita_items', function (Blueprint $table) {
            $table->id();

            // Relación con la cita
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');

            // Campos polimórficos para productos y servicios
            $table->unsignedBigInteger('citable_id');
            $table->string('citable_type'); // App\Models\Producto o App\Models\Servicio

            // Detalles del item
            $table->integer('cantidad')->default(1);
            $table->decimal('precio', 10, 2);
            $table->decimal('descuento', 5, 2)->default(0); // Porcentaje de descuento
            $table->decimal('subtotal', 10, 2);
            $table->decimal('descuento_monto', 10, 2)->default(0);

            // Notas adicionales para el item
            $table->text('notas')->nullable();

            $table->timestamps();

            // Índices de rendimiento
            $table->index(['citable_id', 'citable_type']);
            $table->index(['cita_id', 'cantidad']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cita_items');
    }
};
