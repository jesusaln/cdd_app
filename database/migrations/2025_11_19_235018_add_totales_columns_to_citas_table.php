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
        Schema::table('citas', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->after('estado');
            $table->decimal('descuento_general', 10, 2)->default(0)->after('subtotal');
            $table->decimal('descuento_items', 10, 2)->default(0)->after('descuento_general');
            $table->decimal('iva', 10, 2)->default(0)->after('descuento_items');
            $table->decimal('total', 10, 2)->default(0)->after('iva');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'descuento_general', 'descuento_items', 'iva', 'total']);
        });
    }
};
