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
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carro_id')->constrained()->onDelete('cascade');
            $table->string('tipo');
            $table->date('fecha');
            $table->date('proximo_mantenimiento')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('notas')->nullable();
            $table->decimal('costo', 10, 2)->default(0);
            $table->string('estado')->default('completado'); // completado, pendiente, en_proceso
            $table->integer('kilometraje_actual')->nullable();
            $table->integer('proximo_kilometraje')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
