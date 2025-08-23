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
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();

            // Información básica del equipo
            $table->string('codigo')->unique(); // Código único del equipo (ej: PV-001)
            $table->string('nombre'); // Nombre del equipo (ej: Terminal Punto de Venta Verifone)
            $table->string('marca')->nullable(); // Marca del equipo
            $table->string('modelo')->nullable(); // Modelo específico
            $table->string('numero_serie')->unique()->nullable(); // Número de serie

            // Especificaciones técnicas
            $table->text('descripcion')->nullable(); // Descripción detallada
            $table->json('especificaciones')->nullable(); // Especificaciones técnicas en JSON

            // Información de la renta
            $table->decimal('precio_renta_mensual', 10, 2); // Precio de renta por mes
            $table->decimal('precio_compra', 10, 2)->nullable(); // Precio de compra original
            $table->date('fecha_adquisicion')->nullable(); // Cuándo se adquirió

            // Estado y disponibilidad
            $table->enum('estado', [
                'disponible', // Disponible para rentar
                'rentado', // Actualmente rentado
                'mantenimiento', // En mantenimiento
                'reparacion', // En reparación
                'dado_baja' // Fuera de servicio
            ])->default('disponible');

            $table->enum('condicion', [
                'nuevo',
                'excelente',
                'bueno',
                'regular',
                'malo'
            ])->default('bueno');

            // Información adicional
            $table->string('ubicacion_fisica')->nullable(); // Dónde está almacenado
            $table->text('notas')->nullable(); // Notas adicionales
            $table->json('accesorios')->nullable(); // Lista de accesorios incluidos

            // Información de garantía
            $table->date('fecha_garantia')->nullable(); // Hasta cuándo tiene garantía
            $table->string('proveedor')->nullable(); // Quién lo vendió

            // Metadatos
            $table->timestamps();
            $table->softDeletes(); // Para eliminación suave

            // Índices para optimización
            $table->index(['estado', 'condicion']);
            $table->index('precio_renta_mensual');
            $table->index('fecha_adquisicion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
