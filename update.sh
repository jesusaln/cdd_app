#!/bin/bash

# =============================================================================
# 🔄 Script de Actualización Rápida - CDD App
# =============================================================================
# Uso: ./update.sh
# Para servidores que ya tienen la aplicación desplegada

set -e  # Detener en caso de error

APP_DIR="/opt/cdd_app"

echo "🔄 Iniciando actualización rápida de CDD App..."
echo "📂 Directorio: $APP_DIR"

# =============================================================================
# 1. VERIFICACIONES PREVIAS
# =============================================================================

# Verificar que el directorio existe
if [ ! -d "$APP_DIR" ]; then
    echo "❌ Error: El directorio $APP_DIR no existe."
    echo "💡 Usa deploy.sh para hacer un despliegue completo."
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
# 2. DETENER SERVICIOS
# =============================================================================

echo ""
echo "📋 Paso 1: Deteniendo servicios..."
docker compose down

# =============================================================================
# 3. ACTUALIZAR CÓDIGO
# =============================================================================

echo ""
echo "📥 Paso 2: Actualizando código fuente..."
git pull origin master

# =============================================================================
# 4. RECONSTRUIR Y DESPLEGAR
# =============================================================================

echo ""
echo "🏗️ Paso 3: Reconstruyendo y desplegando..."
DOCKER_BUILDKIT=1 docker compose up -d --build

# Esperar a que la base de datos esté lista
echo "⏳ Esperando a que la base de datos esté lista..."
sleep 10

# =============================================================================
# 5. EJECUTAR MIGRACIONES
# =============================================================================

echo ""
echo "🗄️ Paso 4: Ejecutando migraciones..."
docker compose exec app php artisan migrate --force

# Crear enlaces simbólicos
echo "🔗 Creando enlaces simbólicos..."
docker compose exec app php artisan storage:link || true

# =============================================================================
# 6. VERIFICACIÓN FINAL
# =============================================================================

echo ""
echo "✅ Paso 5: Verificación final..."

# Verificar estado de servicios
echo "🔍 Verificando estado de servicios..."
docker compose ps

# =============================================================================
# 7. FINALIZACIÓN
# =============================================================================

echo ""
echo "🎉 ¡ACTUALIZACIÓN COMPLETADA EXITOSAMENTE!"
echo ""
echo "📊 Información de la actualización:"
echo "   📂 Directorio: $APP_DIR"
echo "   🔄 Código fuente: Actualizado"
echo "   🏗️ Imágenes Docker: Reconstruidas"
echo "   🗄️ Base de datos: Migrada"
echo ""
echo "🔧 Comandos útiles:"
echo "   📋 Ver logs: docker compose logs -f app"
echo "   🔄 Reiniciar: docker compose restart"
echo "   🛑 Detener: docker compose down"
echo ""
echo "✅ ¡Tu aplicación CDD App está actualizada y lista!"