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
        Schema::create('producto_precio_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->decimal('precio_compra_anterior', 10, 2)->nullable();
            $table->decimal('precio_compra_nuevo', 10, 2);
            $table->decimal('precio_venta_anterior', 10, 2)->nullable();
            $table->decimal('precio_venta_nuevo', 10, 2);
            $table->string('tipo_cambio'); // 'creacion', 'manual', 'compra', 'orden_compra'
            $table->text('notas')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_precio_historial');
    }
};
