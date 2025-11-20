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
        Schema::table('producto_series', function (Blueprint $table) {
            $table->foreignId('cita_id')->nullable()->after('almacen_id')->constrained('citas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto_series', function (Blueprint $table) {
            $table->dropForeign(['cita_id']);
            $table->dropColumn('cita_id');
        });
    }
};
