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
        Schema::create('sat_estados', function (Blueprint $table) {
            $table->char('clave', 3)->primary(); // AGU, BCN, SON, ...
            $table->string('nombre', 100);
            $table->string('nombre_corto', 50)->nullable();
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sat_estados');
    }
};