<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('producto_series')) {
            Schema::create('producto_series', function (Blueprint $table) {
                $table->id();
                $table->foreignId('producto_id')->constrained('productos')->cascadeOnUpdate()->restrictOnDelete();
                $table->foreignId('compra_id')->nullable()->constrained('compras')->cascadeOnUpdate()->nullOnDelete();
                $table->foreignId('almacen_id')->nullable()->constrained('almacenes')->cascadeOnUpdate()->nullOnDelete();
                $table->string('numero_serie', 191)->unique();
                $table->string('estado', 30)->default('en_stock');
                $table->timestamps();
                $table->index(['producto_id', 'estado']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_series');
    }
};

