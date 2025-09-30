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
        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained()->cascadeOnDelete();
            $table->enum('tipo', ['entrada', 'salida']);
            $table->unsignedInteger('cantidad');
            $table->integer('stock_anterior');
            $table->integer('stock_posterior');
            $table->string('motivo')->nullable();
            $table->nullableMorphs('referencia');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('detalles')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_movimientos');
    }
};
