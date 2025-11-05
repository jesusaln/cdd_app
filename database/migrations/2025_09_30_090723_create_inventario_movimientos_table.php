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
        if (!Schema::hasTable('inventario_movimientos')) {
            Schema::create('inventario_movimientos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('producto_id')->constrained()->onDelete('cascade');
                $table->enum('tipo', ['entrada', 'salida']);
                $table->integer('cantidad');
                $table->string('motivo');
                $table->string('referencia')->nullable();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->json('metadatos')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_movimientos');
    }
};
