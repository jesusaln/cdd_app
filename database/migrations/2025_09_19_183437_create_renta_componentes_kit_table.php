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
        Schema::create('renta_componentes_kit', function (Blueprint $table) {
            $table->id();

            $table->foreignId('renta_id')->constrained('rentas')->onDelete('cascade');
            $table->foreignId('componente_kit_id')->constrained('componentes_kit')->onDelete('cascade');

            // Información específica de la renta
            $table->decimal('precio_mensual', 10, 2);
            $table->text('notas')->nullable();

            $table->timestamps();

            // Evitar duplicados
            $table->unique(['renta_id', 'componente_kit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('renta_componentes_kit');
    }
};
