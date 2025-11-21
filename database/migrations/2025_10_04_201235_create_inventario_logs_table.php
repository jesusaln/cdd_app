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
        if (!Schema::hasTable('inventario_logs')) {
            Schema::create('inventario_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('producto_id')->constrained('productos');
                $table->foreignId('almacen_id')->constrained('almacenes');
                $table->foreignId('user_id')->nullable()->constrained('users');
                $table->enum('tipo', ['entrada', 'salida']);
                $table->integer('cantidad');
                $table->string('motivo');
                $table->json('detalles')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_logs');
    }
};
