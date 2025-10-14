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
        Schema::create('productos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre');
            $table->text('descripcion');
            $table->string('codigo')->unique();
            $table->string('codigo_barras')->unique();
            $table->string('numero_serie')->nullable()->unique();
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->foreignId('marca_id')->constrained('marcas');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores');
            $table->foreignId('almacen_id')->nullable()->constrained('almacenes');
            $table->integer('stock');
            $table->integer('stock_minimo');
            $table->decimal('precio_compra', 10, 2);
            $table->decimal('precio_venta', 10, 2);
            $table->decimal('impuesto', 5, 2);
            $table->string('unidad_medida');
            $table->date('fecha_vencimiento')->nullable();
            $table->enum('tipo_producto', ['fisico', 'digital']);
            $table->string('imagen')->nullable();
            $table->enum('estado', ['activo', 'inactivo']);
            $table->timestamps();
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
