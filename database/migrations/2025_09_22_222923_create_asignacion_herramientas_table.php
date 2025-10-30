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
        if (Schema::hasTable('asignacion_herramientas')) {
            return;
        }

        Schema::create('asignacion_herramientas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('herramienta_id')->constrained('herramientas')->onDelete('cascade');
            $table->foreignId('tecnico_id')->constrained('tecnicos')->onDelete('cascade');
            $table->enum('tipo_asignacion', ['entrega', 'recepcion']);
            $table->timestamp('fecha_asignacion');
            $table->text('firma_entrega')->nullable();
            $table->text('firma_recepcion')->nullable();
            $table->text('observaciones_entrega')->nullable();
            $table->text('observaciones_recepcion')->nullable();
            $table->string('estado_herramienta_entrega')->nullable();
            $table->string('estado_herramienta_recepcion')->nullable();
            $table->string('foto_estado_entrega')->nullable();
            $table->string('foto_estado_recepcion')->nullable();
            $table->foreignId('usuario_entrega_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('usuario_recepcion_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['herramienta_id', 'activo']);
            $table->index(['tecnico_id', 'activo']);
            $table->index('fecha_asignacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacion_herramientas');
    }
};
