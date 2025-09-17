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
        Schema::table('compras', function (Blueprint $table) {
            // Quién creó / quién actualizó por última vez (nullable y nullOnDelete)
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Quién eliminó (para auditoría con SoftDeletes)
            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Identificador legible/externo (único)
            $table->string('numero_compra', 30)->unique();

            // Campos de cálculo
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('descuento_general', 10, 2)->default(0);
            $table->decimal('descuento_items', 10, 2)->default(0);
            $table->decimal('iva', 10, 2)->nullable();

            // Estado (compatible con enum EstadoCompra)
            $table->string('estado')->default('pendiente')->change();

            // Notas adicionales
            $table->text('notas')->nullable();

            // Soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['deleted_by']);
            $table->dropColumn([
                'created_by',
                'updated_by',
                'deleted_by',
                'numero_compra',
                'subtotal',
                'descuento_general',
                'descuento_items',
                'iva',
                'notas',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
