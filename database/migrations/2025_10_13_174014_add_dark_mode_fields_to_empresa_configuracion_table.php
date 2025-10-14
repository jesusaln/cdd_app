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
        Schema::table('empresa_configuracion', function (Blueprint $table) {
            $table->boolean('dark_mode_enabled')->default(false)->after('dkim_enabled');
            $table->string('dark_mode_primary_color', 7)->default('#1E40AF')->after('dark_mode_enabled');
            $table->string('dark_mode_secondary_color', 7)->default('#3B82F6')->after('dark_mode_primary_color');
            $table->string('dark_mode_background_color', 7)->default('#0F172A')->after('dark_mode_secondary_color');
            $table->string('dark_mode_surface_color', 7)->default('#1E293B')->after('dark_mode_background_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresa_configuracion', function (Blueprint $table) {
            $table->dropColumn([
                'dark_mode_enabled',
                'dark_mode_primary_color',
                'dark_mode_secondary_color',
                'dark_mode_background_color',
                'dark_mode_surface_color',
            ]);
        });
    }
};
