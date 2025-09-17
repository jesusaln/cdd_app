<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_compra_producto', function (Blueprint $t) {
            $t->id();

            // FKs
            $t->foreignId('orden_compra_id')
                ->constrained('orden_compras')
                ->cascadeOnDelete();

            $t->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnDelete();

            // Campos pivote
            $t->unsignedInteger('cantidad');
            $t->decimal('precio', 12, 2);
            $t->decimal('descuento', 12, 2)->default(0); // <- la columna que faltaba

            $t->timestamps();

            // Evita duplicados del mismo producto en la misma OC
            $t->unique(['orden_compra_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_compra_producto');
    }
};
