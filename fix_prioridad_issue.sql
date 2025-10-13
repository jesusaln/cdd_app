-- Script de corrección rápida para el problema de la columna 'prioridad' en tabla citas
-- Ejecutar este script directamente en PostgreSQL

-- 1. Verificar si la columna ya existe
SELECT column_name, data_type
FROM information_schema.columns
WHERE table_name = 'citas' AND column_name = 'prioridad';

-- 2. Si no existe, agregar la columna
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_name = 'citas' AND column_name = 'prioridad'
    ) THEN
        ALTER TABLE citas ADD COLUMN prioridad VARCHAR(255) NULL;
        RAISE NOTICE 'Columna "prioridad" agregada exitosamente a la tabla citas';
    ELSE
        RAISE NOTICE 'La columna "prioridad" ya existe en la tabla citas';
    END IF;
END $$;

-- 3. Verificar que la columna se agregó correctamente
SELECT column_name, data_type, is_nullable
FROM information_schema.columns
WHERE table_name = 'citas' AND column_name = 'prioridad';

-- 4. Mostrar mensaje de confirmación
SELECT 'Script ejecutado correctamente. La columna prioridad está lista.' as resultado;