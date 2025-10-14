<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            Schema::table('clientes', function (Blueprint $table) {
                // Drop existing FULLTEXT if exists
                try {
                    DB::statement('ALTER TABLE clientes DROP INDEX nombre_razon_social_email_rfc_fulltext');
                } catch (\Exception $e) {
                    // Index might not exist, continue
                }

                // Add optimized FULLTEXT index
                DB::statement('ALTER TABLE clientes ADD FULLTEXT nombre_razon_social_email_rfc_fulltext (nombre_razon_social, email, rfc)');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            Schema::table('clientes', function (Blueprint $table) {
                try {
                    DB::statement('ALTER TABLE clientes DROP INDEX nombre_razon_social_email_rfc_fulltext');
                } catch (\Exception $e) {
                    // Index might not exist, continue
                }
            });
        }
    }
};
