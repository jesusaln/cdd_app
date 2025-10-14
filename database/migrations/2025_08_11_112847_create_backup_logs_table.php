<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('path');
            $table->bigInteger('size')->nullable();
            $table->string('type')->default('sql'); // sql, zip
            $table->string('method')->nullable(); // mysqldump, laravel
            $table->string('status')->default('success'); // success, failed
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('deleted_at')->nullable();

            $table->index(['status', 'created_at']);
            $table->index('filename');
        });
    }

    public function down()
    {
        Schema::dropIfExists('backup_logs');
    }
};

