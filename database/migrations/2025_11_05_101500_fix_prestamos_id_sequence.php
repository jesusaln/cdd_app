<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ensure the Postgres sequence for prestamos.id is aligned.
     */
    public function up(): void
    {
        // Works for both serial and identity columns
        DB::statement(<<<'SQL'
DO $$
DECLARE
    seq_name text;
    max_id bigint;
BEGIN
    -- Next value should be MAX(id)+1 (or 1 if table empty)
    SELECT COALESCE(MAX(id), 0) + 1 INTO max_id FROM prestamos;

    -- Try serial sequence first
    SELECT pg_get_serial_sequence('prestamos','id') INTO seq_name;

    IF seq_name IS NOT NULL THEN
        PERFORM setval(seq_name, max_id, false);
    ELSE
        -- Identity column path
        EXECUTE format('ALTER TABLE %I ALTER COLUMN id RESTART WITH %s', 'prestamos', max_id);
    END IF;
END $$;
SQL);
    }

    /**
     * No-op on down; sequence alignment is safe and idempotent.
     */
    public function down(): void
    {
        // intentionally left blank
    }
};

