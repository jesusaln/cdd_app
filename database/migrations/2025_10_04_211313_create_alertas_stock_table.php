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
        if (!Schema::hasTable('alertas_stock')) {
            Schema::create('alertas_stock', function (Blueprint $table) {
                $table->id();
                $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
                $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('cascade');
                $table->enum('tipo', ['bajo', 'critico', 'agotado']);
                $table->integer('stock_actual');
                $table->integer('stock_minimo');
                $table->text('mensaje')->nullable();
                $table->boolean('leida')->default(false);
                $table->timestamp('leida_at')->nullable();
                $table->timestamps();

                // Índices para mejor rendimiento
                $table->index(['producto_id', 'almacen_id']);
                $table->index(['tipo']);
                $table->index(['leida']);
                $table->index(['created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertas_stock');
    }
};
