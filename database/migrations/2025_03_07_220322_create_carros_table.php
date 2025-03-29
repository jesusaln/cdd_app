<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('carros', function (Blueprint $table) {
            $table->id();
            $table->string('marca');
            $table->string('modelo');
            $table->integer('anio');
            $table->string('color');
            $table->decimal('precio', 10, 2);
            $table->string('numero_serie')->unique();
            $table->enum('combustible', ['Gasolina', 'Diésel', 'Eléctrico', 'Híbrido']);
            $table->integer('kilometraje')->default(0);
            $table->string('placa')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carros');
    }
};
