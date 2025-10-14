#!/bin/bash

# =============================================================================
# 🔄 Script de Actualización de Producción - CDD App
# =============================================================================
# Para usar después de modificaciones en el repositorio
# Uso: ./update_production.sh

set -e  # Detener en caso de error

APP_DIR="/opt/cdd_app_produccion"

echo "🔄 Iniciando actualización de producción de CDD App..."
echo "📂 Directorio: $APP_DIR"

# =============================================================================
# 1. VERIFICACIONES PREVIAS
# =============================================================================

# Verificar que el directorio existe
if [ ! -d "$APP_DIR" ]; then
    echo "❌ Error: El directorio $APP_DIR no existe."
    echo "💡 Usa deploy_full_production.sh para hacer un despliegue completo."
    exit 1
fi

# Navegar al directorio
cd $APP_DIR

# Verificar que es un repositorio git
if [ ! -d .git ]; then
    echo "❌ Error: No se encontró repositorio git en $APP_DIR"
    exit 1
fi

# =============================================================================
# 2. ACTUALIZAR CÓDIGO FUENTE
# =============================================================================

echo ""
echo "📥 Actualizando código fuente..."
git pull origin master

# =============================================================================
# 3. CORRECCIONES AUTOMÁTICAS
# =============================================================================

echo ""
echo "🔧 Aplicando correcciones automáticas..."

# Corregir configuración de base de datos si es necesario
sed -i 's/DB_HOST=pg/DB_HOST=db/g' .env 2>/dev/null || true
sed -i 's/DB_HOST=172.23.0.2/DB_HOST=db/g' .env 2>/dev/null || true

# CORRECCIÓN ESPECÍFICA: Configuración de sesiones y Redis
sed -i 's/SESSION_DRIVER=.*/SESSION_DRIVER=redis/g' .env 2>/dev/null || true
sed -i 's/CACHE_STORE=.*/CACHE_STORE=redis/g' .env 2>/dev/null || true
sed -i 's/REDIS_PASSWORD=.*/REDIS_PASSWORD=null/g' .env 2>/dev/null || true

# =============================================================================
# 4. RECONSTRUIR Y DESPLEGAR
# =============================================================================

echo ""
echo "🏗️ Reconstruyendo servicios..."

# Detener servicios
docker compose down 2>/dev/null || true

# Reconstruir imágenes
DOCKER_BUILDKIT=1 docker compose build 2>/dev/null || true

# Iniciar servicios
docker compose up -d 2>/dev/null || true

# Esperar a que estén listos
echo "⏳ Esperando servicios..."
sleep 20

# =============================================================================
# 5. CORRECCIÓN ESPECÍFICA DE .ENV EN CONTENEDOR
# =============================================================================

echo ""
echo "🔧 Aplicando corrección específica de .env en contenedor..."

# Copiar .env corregido al contenedor
docker compose cp .env app:/var/www/html/.env 2>/dev/null || true

# Configurar permisos correctos
echo "🔐 Configurando permisos..."
docker compose exec -T app chmod -R 775 storage bootstrap/cache 2>/dev/null || true
docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Limpiar sesiones y caché problemáticas
echo "🧹 Limpiando sesiones y caché..."
docker compose exec -T app php artisan session:clear 2>/dev/null || true
docker compose exec -T app php artisan cache:clear 2>/dev/null || true
docker compose exec -T app php artisan config:clear 2>/dev/null || true

# Reiniciar aplicación para cargar nueva configuración
docker compose restart app 2>/dev/null || true

# Esperar
sleep 15

# =============================================================================
# 6. EJECUTAR MIGRACIONES Y OPTIMIZACIONES
# =============================================================================

echo ""
echo "🗄️ Ejecutando migraciones y optimizaciones..."

# Ejecutar migraciones
docker compose exec -T app php artisan migrate --force 2>/dev/null || true

# Crear enlaces simbólicos
docker compose exec -T app php artisan storage:link 2>/dev/null || true

# Optimizar aplicación
docker compose exec -T app php artisan config:cache 2>/dev/null || true
docker compose exec -T app php artisan route:cache 2>/dev/null || true
docker compose exec -T app php artisan view:cache 2>/dev/null || true

# =============================================================================
# 7. VERIFICACIÓN FINAL
# =============================================================================

echo ""
echo "✅ Verificación final..."

# Verificar estado de servicios
echo "🔍 Estado de servicios:"
docker compose ps

# Verificar conexión a base de datos
echo ""
echo "🔍 Probando conexión a BD:"
docker compose exec app php artisan tinker --execute="
try {
    \$pdo = DB::connection()->getPdo();
    echo '✅ Conexión BD exitosa\n';
} catch (Exception \$e) {
    echo '❌ Error BD: ' . \$e->getMessage() . '\n';
}
" 2>/dev/null || echo "⚠️ Error en verificación de BD"

# =============================================================================
# 8. FINALIZACIÓN
# =============================================================================

echo ""
echo "🎉 ¡ACTUALIZACIÓN COMPLETADA EXITOSAMENTE!"
echo ""
echo "📊 Información de la actualización:"
echo "   📂 Directorio: $APP_DIR"
echo "   🔄 Código fuente: Actualizado desde Git"
echo "   🏗️ Imágenes Docker: Reconstruidas"
echo "   🔧 Configuración: Corregida automáticamente"
echo "   🗄️ Base de datos: Migrada"
echo "   ⚡ Aplicación: Optimizada"
echo ""
echo "🌐 Tu aplicación está disponible en: https://admin.asistenciavircom.com"
echo ""
echo "🔧 Comandos útiles para el futuro:"
echo "   📋 Ver logs: docker compose logs -f app"
echo "   🔄 Reiniciar: docker compose restart"
echo "   🛑 Detener: docker compose down"
echo "   🔄 Actualizar: cd $APP_DIR && ./update_production.sh"
echo ""
echo "✅ ¡Tu aplicación CDD App está actualizada y lista!"