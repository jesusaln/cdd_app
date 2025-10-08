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
            $table->string('dkim_selector')->nullable()->after('email_reply_to');
            $table->string('dkim_domain')->nullable()->after('dkim_selector');
            $table->text('dkim_public_key')->nullable()->after('dkim_domain');
            $table->boolean('dkim_enabled')->default(false)->after('dkim_public_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresa_configuracion', function (Blueprint $table) {
            $table->dropColumn([
                'dkim_selector',
                'dkim_domain',
                'dkim_public_key',
                'dkim_enabled'
            ]);
        });
    }
};
