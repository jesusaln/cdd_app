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
        Schema::table('backup_logs', function (Blueprint $table) {
            $table->string('checksum', 64)->nullable()->after('metadata');
            $table->boolean('is_encrypted')->default(false)->after('checksum');
            $table->boolean('integrity_verified')->default(false)->after('is_encrypted');
            $table->json('security_warnings')->nullable()->after('integrity_verified');
            $table->unsignedBigInteger('user_id')->nullable()->after('security_warnings');
            $table->index('checksum');
            $table->index('is_encrypted');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('backup_logs', function (Blueprint $table) {
            $table->dropIndex(['checksum']);
            $table->dropIndex(['is_encrypted']);
            $table->dropIndex(['user_id']);
            $table->dropColumn(['checksum', 'is_encrypted', 'integrity_verified', 'security_warnings', 'user_id']);
        });
    }
};
