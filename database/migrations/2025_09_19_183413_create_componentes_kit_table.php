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
        Schema::create('componentes_kit', function (Blueprint $table) {
            $table->id();

            // Información básica del componente
            $table->string('tipo'); // computadora, bascula, lector_codigo_barras, cajon_dinero, sistema, impresora_ticket
            $table->string('nombre');
            $table->text('descripcion')->nullable();

            // Información de inventario
            $table->string('codigo')->unique(); // Código único del componente
            $table->string('numero_serie')->unique()->nullable();
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();

            // Información financiera
            $table->decimal('precio_renta_mensual', 10, 2);
            $table->decimal('precio_compra', 10, 2)->nullable();

            // Estado y disponibilidad
            $table->enum('estado', [
                'disponible',
                'rentado',
                'mantenimiento',
                'reparacion',
                'dado_baja'
            ])->default('disponible');

            $table->enum('condicion', [
                'nuevo',
                'excelente',
                'bueno',
                'regular',
                'malo'
            ])->default('bueno');

            // Información adicional
            $table->date('fecha_adquisicion')->nullable();
            $table->string('ubicacion_fisica')->nullable();
            $table->text('notas')->nullable();
            $table->date('fecha_garantia')->nullable();
            $table->string('proveedor')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['tipo', 'estado']);
            $table->index('codigo');
            $table->index('precio_renta_mensual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('componentes_kit');
    }
};
