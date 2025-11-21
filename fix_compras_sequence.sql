-- Fix PostgreSQL sequence for compras table
-- This script resets the sequence to the correct value based on the maximum ID in the table

-- Reset the sequence to the maximum ID + 1
SELECT setval(
    pg_get_serial_sequence('compras', 'id'),
    COALESCE((SELECT MAX(id) FROM compras), 0) + 1,
    false
);

-- Verify the current sequence value
SELECT currval(pg_get_serial_sequence('compras', 'id')) as current_sequence_value;

-- Show the maximum ID in the table
SELECT MAX(id) as max_id FROM compras;
