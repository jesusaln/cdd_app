<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('venta_item_series')) {
            Schema::create('venta_item_series', function (Blueprint $table) {
                $table->id();
                $table->foreignId('venta_item_id')->constrained('venta_items')->cascadeOnDelete();
                $table->foreignId('producto_serie_id')->constrained('producto_series')->cascadeOnUpdate()->restrictOnDelete();
                $table->string('numero_serie', 191);
                $table->timestamps();

                $table->unique(['venta_item_id', 'producto_serie_id'], 'venta_item_series_unique');
                $table->index(['producto_serie_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_item_series');
    }
};

