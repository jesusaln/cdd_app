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
        if (!Schema::hasTable('movimientos_manuales')) {
            Schema::create('movimientos_manuales', function (Blueprint $table) {
                $table->id();
                $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
                $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->enum('tipo', ['entrada', 'salida']);
                $table->integer('cantidad');
                $table->decimal('costo_unitario', 10, 2)->nullable();
                $table->decimal('total', 10, 2)->nullable();
                $table->string('categoria')->nullable(); // recepcion, donacion, merma, consumo, etc.
                $table->text('motivo')->nullable();
                $table->text('observaciones')->nullable();
                $table->string('referencia')->nullable(); // numero de documento, factura, etc.
                $table->timestamps();

                // Índices para mejor rendimiento
                $table->index(['producto_id', 'almacen_id']);
                $table->index(['user_id']);
                $table->index(['tipo']);
                $table->index(['categoria']);
                $table->index(['created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_manuales');
    }
};
