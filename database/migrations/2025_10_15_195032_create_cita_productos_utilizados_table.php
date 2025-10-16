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
        Schema::create('cita_productos_utilizados', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');

            // Información del producto utilizado
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 12, 2)->nullable();
            $table->enum('tipo_uso', ['repuesto', 'consumible', 'herramienta', 'otro'])->default('repuesto');
            $table->text('notas')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['cita_id', 'producto_id']);
            $table->index(['producto_id', 'tipo_uso']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cita_productos_utilizados');
    }
};
