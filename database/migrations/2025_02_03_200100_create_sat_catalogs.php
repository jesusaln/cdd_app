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
        Schema::create('sat_regimenes_fiscales', function (Blueprint $table) {
            $table->char('clave', 3)->primary(); // p.ej. 601, 612, ...
            $table->string('descripcion', 255);
            // Para validar compatibilidad con persona física/moral
            $table->boolean('persona_fisica')->default(true);
            $table->boolean('persona_moral')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sat_regimenes_fiscales');
    }
};