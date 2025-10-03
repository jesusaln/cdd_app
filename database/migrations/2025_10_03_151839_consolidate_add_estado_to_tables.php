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
        // Add estado to categorias
        Schema::table('categorias', function (Blueprint $table) {
            $table->enum('estado', ['activo', 'inactivo'])->default('activo')->after('descripcion');
        });

        // Add estado to marcas
        Schema::table('marcas', function (Blueprint $table) {
            $table->enum('estado', ['activo', 'inactivo'])->default('activo')->after('descripcion');
        });

        // Add estado to almacenes
        Schema::table('almacenes', function (Blueprint $table) {
            $table->enum('estado', ['activo', 'inactivo'])->default('activo')->after('responsable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove estado from categorias
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn('estado');
        });

        // Remove estado from marcas
        Schema::table('marcas', function (Blueprint $table) {
            $table->dropColumn('estado');
        });

        // Remove estado from almacenes
        Schema::table('almacenes', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
