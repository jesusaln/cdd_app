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
        if (!Schema::hasColumn('compras', 'orden_compra_id')) {
            Schema::table('compras', function (Blueprint $table) {
                $table->foreignId('orden_compra_id')->nullable()->constrained('orden_compras')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            if (Schema::hasColumn('compras', 'orden_compra_id')) {
                $table->dropForeign(['orden_compra_id']);
                $table->dropColumn('orden_compra_id');
            }
        });
    }
};
