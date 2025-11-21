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
        if (!Schema::hasTable('lotes')) {
            Schema::create('lotes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('producto_id')->constrained()->onDelete('cascade');
                $table->string('numero_lote');
                $table->date('fecha_caducidad')->nullable();
                $table->integer('cantidad_inicial');
                $table->integer('cantidad_actual');
                $table->decimal('costo_unitario', 10, 2)->nullable();
                $table->timestamps();

                $table->unique(['producto_id', 'numero_lote']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
