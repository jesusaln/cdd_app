#!/bin/bash

# =============================================================================
# 🚀 Script de Despliegue Rápido - CDD App
# =============================================================================
# Uso: ./deploy.sh [nombre_del_proyecto]
# Ejemplo: ./deploy.sh cdd_app_produccion

set -e  # Detener en caso de error

PROJECT_NAME="${1:-cdd_app}"
APP_DIR="/opt/$PROJECT_NAME"

echo "🚀 Iniciando despliegue rápido de CDD App..."
echo "📁 Proyecto: $PROJECT_NAME"
echo "📂 Directorio: $APP_DIR"

# =============================================================================
# 1. PREPARACIÓN DEL ENTORNO
# =============================================================================

echo ""
echo "📋 Paso 1: Preparando entorno..."

# Crear directorio si no existe
sudo mkdir -p $APP_DIR

# Navegar al directorio
cd $APP_DIR

# Detener servicios existentes si los hay
echo "🔄 Deteniendo servicios existentes..."
docker compose down -v --remove-orphans 2>/dev/null || true
docker image prune -f 2>/dev/null || true

# =============================================================================
# 2. OBTENER CÓDIGO FUENTE
# =============================================================================

echo ""
echo "📥 Paso 2: Obteniendo código fuente..."

# Clonar o actualizar repositorio
if [ ! -d .git ]; then
    echo "📋 Clonando repositorio por primera vez..."
    git clone https://github.com/jesusaln/cdd_app.git .
else
    echo "📋 Actualizando repositorio existente..."
    git pull origin master
fi

# =============================================================================
# 3. CONFIGURACIÓN INICIAL
# =============================================================================

echo ""
echo "⚙️ Paso 3: Configuración inicial..."

# Copiar archivo de entorno si no existe
if [ ! -f .env ]; then
    echo "📋 Creando archivo .env..."
    cp .env.example .env 2>/dev/null || echo "⚠️ No se encontró .env.example"
fi

# Generar clave de aplicación si no existe
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "🔑 Generando clave de aplicación..."
    docker run --rm -it -v $(pwd):/app -w /app php:8.3-cli php artisan key:generate
fi

# =============================================================================
# 4. CONSTRUCCIÓN Y DESPLIEGUE
# =============================================================================

echo ""
echo "🏗️ Paso 4: Construyendo y desplegando..."

# Construir e iniciar servicios
echo "🔨 Construyendo imágenes Docker..."
DOCKER_BUILDKIT=1 docker compose up -d --build

# Esperar a que la base de datos esté lista
echo "⏳ Esperando a que la base de datos esté lista..."
sleep 10

# =============================================================================
# 5. EJECUTAR MIGRACIONES
# =============================================================================

echo ""
echo "🗄️ Paso 5: Ejecutando migraciones..."

# Ejecutar migraciones
echo "📊 Aplicando migraciones de base de datos..."
docker compose exec app php artisan migrate:fresh --force

# Crear enlaces simbólicos
echo "🔗 Creando enlaces simbólicos..."
docker compose exec app php artisan storage:link || true

# =============================================================================
# 6. VERIFICACIÓN FINAL
# =============================================================================

echo ""
echo "✅ Paso 6: Verificación final..."

# Verificar estado de servicios
echo "🔍 Verificando estado de servicios..."
docker compose ps

# Verificar estado de migraciones
echo "📋 Verificando migraciones..."
docker compose exec app php artisan migrate:status

# =============================================================================
# 7. FINALIZACIÓN
# =============================================================================

echo ""
echo "🎉 ¡DESPLIEGUE COMPLETADO EXITOSAMENTE!"
echo ""
echo "📊 Información del despliegue:"
echo "   🖥️ Proyecto: $PROJECT_NAME"
echo "   📂 Directorio: $APP_DIR"
echo "   🌐 Acceso: http://localhost (o la URL configurada)"
echo ""
echo "🔧 Comandos útiles para el futuro:"
echo "   📋 Ver logs: docker compose logs -f app"
echo "   🔄 Reiniciar: docker compose restart"
echo "   🛑 Detener: docker compose down"
echo "   🔄 Actualizar: cd $APP_DIR && git pull && docker compose up -d --build"
echo ""
echo "✅ ¡Tu aplicación CDD App está lista para usar!"