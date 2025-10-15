<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Nueva migración consolidada para tabla productos
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            // Información básica del producto
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('codigo')->unique();
            $table->string('codigo_barras')->unique();
            $table->string('numero_serie')->nullable()->unique();

            // Relaciones con otras tablas
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->foreignId('marca_id')->constrained('marcas');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores');
            $table->foreignId('almacen_id')->nullable()->constrained('almacenes');

            // Control de inventario
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->integer('reservado')->default(0);

            // Información de precios y ganancias
            $table->decimal('precio_compra', 10, 2)->default(0);
            $table->decimal('precio_venta', 10, 2)->default(0);
            $table->decimal('margen_ganancia', 5, 2)->default(0); // porcentaje
            $table->decimal('comision_vendedor', 5, 2)->default(0); // porcentaje
            $table->decimal('impuesto', 5, 2)->default(0);

            // Información adicional
            $table->string('unidad_medida')->default('pieza');
            $table->date('fecha_vencimiento')->nullable();
            $table->boolean('expires')->default(false);
            $table->enum('tipo_producto', ['fisico', 'digital'])->default('fisico');
            $table->string('imagen')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');

            // Timestamps
            $table->timestamps();

            // Índices de rendimiento
            $table->index('nombre');
            $table->index('codigo');
            $table->index('categoria_id');
            $table->index('estado');
            $table->index(['estado', 'categoria_id']);
            $table->index(['stock', 'stock_minimo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
