<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Crea la tabla 'orden_compras' (NOMBRE DE TABLA CORREGIDO)
        Schema::create('orden_compras', function (Blueprint $table) {
            $table->id(); // Columna de ID primario autoincremental
            $table->foreignId('proveedor_id') // Clave foránea para el proveedor
                ->constrained('proveedores') // Hace referencia a la tabla 'proveedores'
                ->onDelete('cascade'); // Si se elimina un proveedor, se eliminan sus órdenes de compra

            $table->decimal('total', 10, 2); // Columna para el total de la orden, con 10 dígitos en total y 2 decimales
            $table->string('estado')->default('pendiente'); // Columna para el estado de la orden (ej. 'pendiente', 'recibida', 'cancelada')
            $table->timestamp('fecha_recepcion')->nullable(); // Campo opcional para registrar la fecha de recepción de la orden
            $table->timestamps(); // Columnas `created_at` y `updated_at`
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Elimina la tabla 'orden_compras' si se revierte la migración
        Schema::dropIfExists('orden_compras');
    }
};
