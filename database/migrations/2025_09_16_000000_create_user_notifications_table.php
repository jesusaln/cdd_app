<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 50)->default('system');
            $table->string('title', 200);
            $table->text('message')->nullable();
            $table->json('data')->nullable();
            $table->string('action_url', 500)->nullable();
            $table->string('icon', 100)->nullable();
            // 🔑 Solo usamos read_at para saber si está leída
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_notifications');
    }
};
