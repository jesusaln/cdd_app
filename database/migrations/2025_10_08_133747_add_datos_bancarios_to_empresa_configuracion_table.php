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
        Schema::table('empresa_configuracion', function (Blueprint $table) {
            $table->string('banco')->nullable()->after('pais')->default('Banamex');
            $table->string('sucursal')->nullable()->after('banco')->default('7008');
            $table->string('cuenta')->nullable()->after('sucursal')->default('5952062');
            $table->string('clabe')->nullable()->after('cuenta')->default('002760700859520625');
            $table->string('titular')->nullable()->after('clabe')->default('Jesús Alberto López Noriega');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresa_configuracion', function (Blueprint $table) {
            $table->string('banco')->nullable()->after('pais')->default('Banamex');
            $table->string('sucursal')->nullable()->after('banco')->default('7008');
            $table->string('cuenta')->nullable()->after('sucursal')->default('5952062');
            $table->string('clabe')->nullable()->after('cuenta')->default('002760700859520625');
            $table->string('titular')->nullable()->after('clabe')->default('Jesús Alberto López Noriega');
        });
    }
};
