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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();

            // FK proveedor
            $table->foreignId('proveedor_id')
                ->constrained('proveedores')
                ->cascadeOnDelete();

            // Auditoría (nullable + nullOnDelete)
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()
                ->constrained('users')->nullOnDelete();

            // Identificador legible/externo
            $table->string('numero_compra', 30)->unique();

            // Cálculos
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('descuento_general', 10, 2)->default(0);
            $table->decimal('descuento_items', 10, 2)->default(0);
            $table->decimal('iva', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->default(0);

            // Estado (si luego quieres ENUM real en MySQL, aquí string funciona en ambos motores)
            $table->string('estado')->default('pendiente');

            // Notas
            $table->text('notas')->nullable();

            // Soft deletes + timestamps
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
