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
        Schema::table('empresas', function (Blueprint $table) {
            $table->boolean('whatsapp_enabled')->default(false);
            $table->string('whatsapp_business_account_id')->nullable();
            $table->string('whatsapp_phone_number_id')->nullable();
            $table->string('whatsapp_sender_phone')->nullable(); // E.164 format
            $table->text('whatsapp_access_token')->nullable();
            $table->string('whatsapp_app_secret')->nullable();
            $table->string('whatsapp_webhook_verify_token')->nullable();
            $table->string('whatsapp_default_language')->default('es_MX');
            $table->string('whatsapp_template_payment_reminder')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_enabled',
                'whatsapp_business_account_id',
                'whatsapp_phone_number_id',
                'whatsapp_sender_phone',
                'whatsapp_access_token',
                'whatsapp_app_secret',
                'whatsapp_webhook_verify_token',
                'whatsapp_default_language',
                'whatsapp_template_payment_reminder',
            ]);
        });
    }
};
