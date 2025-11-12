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
        // Safe semantics for empty tables (set to 1 and is_called=false)
        DB::statement(
            "SELECT setval(\n" .
            "  pg_get_serial_sequence('users','id'),\n" .
            "  COALESCE(NULLIF((SELECT MAX(id) FROM users), 0), 1),\n" .
            "  (SELECT COUNT(*)>0 FROM users)\n" .
            ")"
        );

        DB::statement(
            "SELECT setval(\n" .
            "  pg_get_serial_sequence('roles','id'),\n" .
            "  COALESCE(NULLIF((SELECT MAX(id) FROM roles), 0), 1),\n" .
            "  (SELECT COUNT(*)>0 FROM roles)\n" .
            ")"
        );
    }

    public function down(): void
    {
        // No-op: sequence alignment does not need rollback
    }
};
