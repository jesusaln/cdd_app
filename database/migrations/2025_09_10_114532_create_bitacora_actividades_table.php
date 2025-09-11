<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora_actividades', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('cliente_id')
                ->nullable()
                ->constrained('clientes')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('titulo', 160);
            $table->text('descripcion')->nullable();

            $table->date('fecha');
            $table->time('hora')->nullable();

            $table->dateTime('inicio_at')->nullable();
            $table->dateTime('fin_at')->nullable();

            $table->enum('tipo', ['soporte','mantenimiento','instalacion','cotizacion','visita','administrativo', 'cobranza', 'otro'])->default('soporte');
            $table->enum('estado', ['pendiente','en_proceso','completado','cancelado'])->default('completado');
            $table->unsignedTinyInteger('prioridad')->default(3);

            $table->string('ubicacion', 180)->nullable();
            $table->json('adjuntos')->nullable();

            $table->boolean('es_facturable')->default(false);
            $table->decimal('costo_mxn', 10, 2)->nullable();

            $table->timestamps();

            // Índices regulares
            $table->index(['fecha']);
            $table->index(['user_id']);
            $table->index(['cliente_id']);
            $table->index(['tipo']);

            // ❌ NO usar fullText en SQLite
            // $table->fullText(['titulo','descripcion']); // <-- solo MySQL

            // Fallback: indexa solo 'titulo' (string). 'descripcion' es TEXT — mejor no indexarlo en SQLite.
            $table->index(['titulo']);
        });

        // Si quieres fulltext en MySQL, puedes agregarlo condicional:
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('bitacora_actividades', function (Blueprint $table) {
                $table->fullText(['titulo','descripcion']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora_actividades');
    }
};
