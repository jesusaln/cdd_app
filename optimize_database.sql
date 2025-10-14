-- Script de optimización para PostgreSQL con miles de registros
-- Ejecutar después de migrar datos al VPS

-- =====================================================
-- OPTIMIZACIONES DE RENDIMIENTO
-- =====================================================

-- 1. Crear índices estratégicos para consultas frecuentes
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_clientes_created_at ON clientes (created_at DESC);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_clientes_nombre ON clientes (nombre);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_clientes_telefono ON clientes (telefono);

CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_productos_stock ON productos (stock);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_productos_nombre ON productos (nombre);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_productos_categoria ON productos (categoria_id);

CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_ventas_fecha ON ventas (fecha DESC);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_ventas_cliente ON ventas (cliente_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_ventas_total ON ventas (total);

CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_pedidos_fecha ON pedidos (fecha DESC);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_pedidos_estado ON pedidos (estado);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_pedidos_cliente ON pedidos (cliente_id);

-- 2. Índices compuestos para consultas complejas
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_ventas_fecha_cliente ON ventas (fecha DESC, cliente_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_pedidos_fecha_estado ON pedidos (fecha DESC, estado);

-- 3. Configurar autovacuum para tablas grandes
ALTER TABLE clientes SET (autovacuum_vacuum_scale_factor = 0.05);
ALTER TABLE productos SET (autovacuum_vacuum_scale_factor = 0.05);
ALTER TABLE ventas SET (autovacuum_vacuum_scale_factor = 0.02);
ALTER TABLE pedidos SET (autovacuum_vacuum_scale_factor = 0.02);

-- 4. Configurar fillfactor para tablas con muchas actualizaciones
ALTER TABLE ventas SET (fillfactor = 85);
ALTER TABLE pedidos SET (fillfactor = 85);
ALTER TABLE productos SET (fillfactor = 90);

-- =====================================================
-- OPTIMIZACIONES DE CONSULTAS
-- =====================================================

-- 1. Vista para estadísticas rápidas
CREATE OR REPLACE VIEW vista_estadisticas_ventas AS
SELECT
    DATE_TRUNC('month', fecha) as mes,
    COUNT(*) as total_ventas,
    SUM(total) as monto_total,
    AVG(total) as promedio_venta
FROM ventas
GROUP BY DATE_TRUNC('month', fecha)
ORDER BY mes DESC;

-- 2. Vista para productos bajos en stock
CREATE OR REPLACE VIEW vista_productos_stock_bajo AS
SELECT
    p.*,
    CASE
        WHEN p.stock <= p.stock_minimo THEN 'CRÍTICO'
        WHEN p.stock <= p.stock_minimo * 1.5 THEN 'BAJO'
        ELSE 'NORMAL'
    END as estado_stock
FROM productos p
WHERE p.stock <= p.stock_minimo * 2
ORDER BY p.stock ASC;

-- =====================================================
-- MANTENIMIENTO Y MONITOREO
-- =====================================================

-- 1. Función para limpiar datos antiguos (opcional)
CREATE OR REPLACE FUNCTION limpiar_datos_antiguos(dias_antiguedad INTEGER DEFAULT 365)
RETURNS INTEGER AS $$
DECLARE
    registros_eliminados INTEGER;
BEGIN
    -- Eliminar ventas antiguas (mantener últimos 2 años por defecto)
    DELETE FROM ventas WHERE fecha < NOW() - INTERVAL '2 years';

    -- Eliminar logs antiguos si existen
    DELETE FROM auditoria WHERE created_at < NOW() - INTERVAL '1 year';

    GET DIAGNOSTICS registros_eliminados = ROW_COUNT;

    RETURN registros_eliminados;
END;
$$ LANGUAGE plpgsql;

-- 2. Configurar mantenimiento automático
REINDEX DATABASE CONCURRENTLY cdd_app_prod;

-- Ejecutar VACUUM ANALYZE para actualizar estadísticas
VACUUM ANALYZE clientes;
VACUUM ANALYZE productos;
VACUUM ANALYZE ventas;
VACUUM ANALYZE pedidos;

-- =====================================================
-- CONFIGURACIÓN DE MEMORIA Y RENDIMIENTO
-- =====================================================

-- Configuraciones recomendadas para postgresql.conf (ajustar según recursos del VPS)

-- Memoria compartida (25% de RAM total)
-- shared_buffers = 1GB

-- Memoria de trabajo (50% de RAM disponible después de shared_buffers)
-- work_mem = 128MB

-- Mantenimiento (5% de RAM)
-- maintenance_work_mem = 256MB

-- Checkpoint (para discos lentos)
-- checkpoint_segments = 128
-- checkpoint_completion_target = 0.9

-- Conexiones
-- max_connections = 100

-- =====================================================
-- VERIFICACIÓN DE OPTIMIZACIONES
-- =====================================================

-- Ver índices creados
SELECT
    schemaname,
    tablename,
    indexname,
    indexdef
FROM pg_indexes
WHERE schemaname = 'public'
ORDER BY tablename, indexname;

-- Ver tamaño de tablas e índices
SELECT
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as tamaño_total,
    pg_size_pretty(pg_relation_size(schemaname||'.'||tablename)) as tamaño_tabla,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename) - pg_relation_size(schemaname||'.'||tablename)) as tamaño_indices
FROM pg_tables
WHERE schemaname = 'public'
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;

-- Ver estadísticas de tablas
SELECT
    schemaname,
    tablename,
    n_tup_ins as inserciones,
    n_tup_upd as actualizaciones,
    n_tup_del as eliminaciones,
    n_live_tup as filas_activas,
    n_dead_tup as filas_muertas
FROM pg_stat_user_tables
WHERE schemaname = 'public'
ORDER BY n_live_tup DESC;

-- =====================================================
-- COMENTARIOS Y DOCUMENTACIÓN
-- =====================================================

COMMENT ON DATABASE cdd_app_prod IS 'Base de datos de producción - Climas del Desierto - Optimizada para alto volumen de datos';

COMMENT ON INDEX idx_clientes_created_at IS 'Índice para consultas por fecha de creación de clientes';
COMMENT ON INDEX idx_productos_stock IS 'Índice para consultas de productos por stock';
COMMENT ON INDEX idx_ventas_fecha IS 'Índice para consultas de ventas por fecha';
COMMENT ON INDEX idx_pedidos_estado IS 'Índice para filtrar pedidos por estado';

COMMENT ON VIEW vista_estadisticas_ventas IS 'Vista para estadísticas mensuales de ventas - optimizada para dashboards';
COMMENT ON VIEW vista_productos_stock_bajo IS 'Vista para monitoreo de productos con stock bajo';

-- =====================================================
-- EJECUCIÓN FINAL
-- =====================================================

-- Ejecutar análisis final para actualizar estadísticas del optimizador
ANALYZE;

-- Mostrar resumen de optimizaciones aplicadas
SELECT 'Optimizaciones aplicadas exitosamente' as resultado;