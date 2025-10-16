<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('productos') && !Schema::hasColumn('productos', 'requiere_serie')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->boolean('requiere_serie')->default(false)->after('expires');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('productos') && Schema::hasColumn('productos', 'requiere_serie')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->dropColumn('requiere_serie');
            });
        }
    }
};

