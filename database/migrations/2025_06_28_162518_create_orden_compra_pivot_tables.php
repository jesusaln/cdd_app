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
        // Crea la tabla pivote para Ordenes de Compra y Productos
        Schema::create('orden_compra_producto', function (Blueprint $table) {
            $table->foreignId('orden_compra_id') // Clave foránea para la orden de compra
                ->constrained('orden_compras') // <-- HACE REFERENCIA A LA TABLA CORREGIDA
                ->onDelete('cascade'); // Si se elimina una orden de compra, se eliminan sus asociaciones

            $table->foreignId('producto_id') // Clave foránea para el producto
                ->constrained('productos') // Hace referencia a la tabla 'productos'
                ->onDelete('cascade'); // Si se elimina un producto, se eliminan sus asociaciones

            $table->integer('cantidad'); // Cantidad del producto en esta orden
            $table->decimal('precio', 10, 2); // Precio de compra del producto en esta orden

            $table->primary(['orden_compra_id', 'producto_id']); // Define una clave primaria compuesta para evitar duplicados
        });

        // Crea la tabla pivote para Ordenes de Compra y Servicios (si aplica)
        Schema::create('orden_compra_servicio', function (Blueprint $table) {
            $table->foreignId('orden_compra_id') // Clave foránea para la orden de compra
                ->constrained('orden_compras') // <-- HACE REFERENCIA A LA TABLA CORREGIDA
                ->onDelete('cascade'); // Si se elimina una orden de compra, se eliminan sus asociaciones

            $table->foreignId('servicio_id') // Clave foránea para el servicio
                ->constrained('servicios') // Hace referencia a la tabla 'servicios'
                ->onDelete('cascade'); // Si se elimina un servicio, se eliminan sus asociaciones

            $table->integer('cantidad'); // Cantidad del servicio en esta orden
            $table->decimal('precio', 10, 2); // Precio de compra del servicio en esta orden

            $table->primary(['orden_compra_id', 'servicio_id']); // Define una clave primaria compuesta
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Elimina las tablas pivote si se revierte la migración
        Schema::dropIfExists('orden_compra_producto');
        Schema::dropIfExists('orden_compra_servicio');
    }
};
