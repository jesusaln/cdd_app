<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Align sequences for pagos-related tables
        $this->alignSequence('pagos_prestamos');
        $this->alignSequence('historial_pagos_prestamos');
    }

    public function down(): void
    {
        // no-op (idempotent adjustment)
    }

    private function alignSequence(string $table): void
    {
        DB::statement(<<<SQL
DO $$
DECLARE
    seq_name text;
    max_id bigint;
    tbl text := '{$table}';
BEGIN
    EXECUTE format('SELECT COALESCE(MAX(id), 0) + 1 FROM %I', tbl) INTO max_id;
    EXECUTE format('SELECT pg_get_serial_sequence(''%I'',''id'')', tbl) INTO seq_name;

    IF seq_name IS NOT NULL THEN
        PERFORM setval(seq_name, max_id, false);
    ELSE
        EXECUTE format('ALTER TABLE %I ALTER COLUMN id RESTART WITH %s', tbl, max_id);
    END IF;
END $$;
SQL);
    }
};

