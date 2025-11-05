<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ajustes_vacaciones')) {
            Schema::create('ajustes_vacaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->integer('anio');
                $table->integer('dias');
                $table->string('motivo', 500)->nullable();
                $table->foreignId('creado_por')->constrained('users')->onDelete('set null')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'anio']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ajustes_vacaciones');
    }
};

