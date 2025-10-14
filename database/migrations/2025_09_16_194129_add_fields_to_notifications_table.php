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
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id');
            $table->string('title')->after('type');
            $table->text('message')->after('title');
            $table->timestamp('read_at')->nullable()->after('read');
            $table->string('action_url')->nullable()->after('read_at');
            $table->string('icon')->nullable()->after('action_url');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'read']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'read']);
            $table->dropIndex(['type']);
            $table->dropColumn(['user_id', 'title', 'message', 'read_at', 'action_url', 'icon']);
        });
    }
};
