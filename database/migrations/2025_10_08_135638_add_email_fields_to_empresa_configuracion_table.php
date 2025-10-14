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
            $table->string('smtp_host')->nullable()->after('informacion_adicional_bancaria');
            $table->integer('smtp_port')->nullable()->after('smtp_host');
            $table->string('smtp_username')->nullable()->after('smtp_port');
            $table->string('smtp_password')->nullable()->after('smtp_username');
            $table->string('smtp_encryption')->nullable()->after('smtp_password');
            $table->string('email_from_address')->nullable()->after('smtp_encryption');
            $table->string('email_from_name')->nullable()->after('email_from_address');
            $table->string('email_reply_to')->nullable()->after('email_from_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresa_configuracion', function (Blueprint $table) {
            $table->dropColumn([
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password',
                'smtp_encryption',
                'email_from_address',
                'email_from_name',
                'email_reply_to'
            ]);
        });
    }
};
