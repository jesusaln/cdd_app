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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->string('to')->index(); // número destino E.164
            $table->string('template_name')->nullable();
            $table->json('template_params')->nullable();
            $table->string('message_id')->nullable();
            $table->string('status')->nullable(); // queued/sent/delivered/failed/read
            $table->text('response')->nullable(); // raw API response
            $table->string('error_code')->nullable();
            $table->timestamps();

            // Índices adicionales para búsquedas comunes
            $table->index(['empresa_id', 'status']);
            $table->index(['to', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
