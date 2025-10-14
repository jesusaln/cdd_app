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
        Schema::create('renta_equipos', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('renta_id')->constrained('rentas')->onDelete('cascade');
            $table->foreignId('equipo_id')->constrained('equipos')->onDelete('cascade');

            // Información específica de este equipo en esta renta
            $table->decimal('precio_unitario', 10, 2); // Precio específico para esta renta
            $table->integer('cantidad')->default(1); // Cantidad de este equipo
            $table->decimal('subtotal', 10, 2); // cantidad * precio_unitario

            // Condición al momento de la renta
            $table->enum('condicion_entrega', [
                'nuevo',
                'excelente',
                'bueno',
                'regular'
            ]);

            $table->enum('condicion_devolucion', [
                'nuevo',
                'excelente',
                'bueno',
                'regular',
                'malo',
                'no_devuelto'
            ])->nullable();

            // Información adicional
            $table->text('notas_entrega')->nullable(); // Observaciones al entregar
            $table->text('notas_devolucion')->nullable(); // Observaciones al devolver
            $table->json('accesorios_incluidos')->nullable(); // Lista de accesorios entregados
            $table->json('accesorios_devueltos')->nullable(); // Lista de accesorios devueltos

            // Fechas específicas
            $table->date('fecha_entrega')->nullable(); // Cuándo se entregó este equipo específico
            $table->date('fecha_devolucion')->nullable(); // Cuándo se devolvió

            // Metadatos
            $table->timestamps();

            // Clave única compuesta
            $table->unique(['renta_id', 'equipo_id']);

            // Índices
            $table->index('renta_id');
            $table->index('equipo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('renta_equipos');
    }
};
