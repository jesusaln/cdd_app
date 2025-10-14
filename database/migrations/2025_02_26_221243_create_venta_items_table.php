<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('venta_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->morphs('ventable'); // Crea ventable_id y ventable_type
            $table->integer('cantidad')->unsigned();
            $table->decimal('precio', 10, 2);
            $table->decimal('descuento', 5, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('descuento_monto', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('venta_items');
    }
};
