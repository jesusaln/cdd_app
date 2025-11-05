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
        if (!Schema::hasTable('ajustes_inventario')) {
            Schema::create('ajustes_inventario', function (Blueprint $table) {
                $table->id();
                $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
                $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->enum('tipo', ['incremento', 'decremento']);
                $table->integer('cantidad_anterior');
                $table->integer('cantidad_ajuste');
                $table->integer('cantidad_nueva');
                $table->text('motivo')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();

                // Índices para mejor rendimiento
                $table->index(['producto_id', 'almacen_id']);
                $table->index(['user_id']);
                $table->index(['created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ajustes_inventario');
    }
};
