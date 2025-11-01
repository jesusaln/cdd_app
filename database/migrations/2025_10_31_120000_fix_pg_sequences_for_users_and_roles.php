<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only applicable to PostgreSQL
        try {
            $driver = DB::getDriverName();
        } catch (\Throwable $e) {
            $driver = null;
        }

        if ($driver !== 'pgsql') {
            return;
        }

        // Realign sequences for users and roles so nextval doesn't collide with existing ids
        // Using explicit table names to avoid SQL injection and to stay focused on reported errors
        DB::statement("SELECT setval(pg_get_serial_sequence('users','id'), COALESCE((SELECT MAX(id) FROM users), 0), true)");
        DB::statement("SELECT setval(pg_get_serial_sequence('roles','id'), COALESCE((SELECT MAX(id) FROM roles), 0), true)");
    }

    public function down(): void
    {
        // No-op: sequence alignment does not need rollback
    }
};

