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
        Schema::create('sat_usos_cfdi', function (Blueprint $table) {
            $table->char('clave', 3)->primary(); // p.ej. G01, G03, D01...
            $table->string('descripcion', 255);
            $table->boolean('persona_fisica')->default(true);
            $table->boolean('persona_moral')->default(true);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('sat_usos_cfdi');
    }
};
