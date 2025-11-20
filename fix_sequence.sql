-- Resetear secuencia de inventario_movimientos
SELECT setval('inventario_movimientos_id_seq', COALESCE((SELECT MAX(id) FROM inventario_movimientos), 1), true);
