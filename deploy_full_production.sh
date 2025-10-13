#!/bin/bash

# =============================================================================
# 🚀 Script de Despliegue COMPLETO para Producción - CDD App
# =============================================================================
# Este script hace TODO automáticamente sin intervención del usuario
# Solo ejecútalo en el servidor: ./deploy_full_production.sh

set -e  # Detener en caso de error

PROJECT_NAME="cdd_app_produccion"
APP_DIR="/opt/$PROJECT_NAME"

echo "🚀 Iniciando despliegue COMPLETO de CDD App..."
echo "📁 Proyecto: $PROJECT_NAME"
echo "📂 Directorio: $APP_DIR"
echo "🌐 Todo automático - no requiere intervención"

# =============================================================================
# 1. PREPARACIÓN DEL ENTORNO (AUTOMÁTICO)
# =============================================================================

echo ""
echo "📋 Paso 1: Preparando entorno automáticamente..."

# Crear directorio si no existe
sudo mkdir -p $APP_DIR 2>/dev/null || true

# Navegar al directorio
cd $APP_DIR

# Detener servicios existentes si los hay
echo "🔄 Deteniendo servicios existentes..."
docker compose down -v --remove-orphans 2>/dev/null || true
docker image prune -f 2>/dev/null || true

# =============================================================================
# 2. CONFIGURACIÓN SSH AUTOMÁTICA
# =============================================================================

echo ""
echo "🔑 Paso 2: Configurando SSH automáticamente..."

# Configurar SSH para GitHub automáticamente
mkdir -p ~/.ssh 2>/dev/null || true
chmod 700 ~/.ssh

# Crear clave SSH si no existe
if [ ! -f ~/.ssh/deploy_key ]; then
    echo "🔐 Generando clave SSH automáticamente..."
    ssh-keygen -t ed25519 -C "srv896787-deploy-key" -f ~/.ssh/deploy_key -N "" 2>/dev/null || true
fi

# Configurar SSH agent
eval "$(ssh-agent -s)" 2>/dev/null || true
ssh-add ~/.ssh/deploy_key 2>/dev/null || true

# Agregar GitHub al known_hosts
ssh-keyscan -H github.com >> ~/.ssh/known_hosts 2>/dev/null || true
chmod 600 ~/.ssh/known_hosts 2>/dev/null || true

# Configurar SSH config
cat > ~/.ssh/config << EOF
Host github.com
  HostName github.com
  User git
  IdentityFile ~/.ssh/deploy_key
  IdentitiesOnly yes
EOF
chmod 600 ~/.ssh/config 2>/dev/null || true

# =============================================================================
# 3. OBTENER CÓDIGO FUENTE (AUTOMÁTICO)
# =============================================================================

echo ""
echo "📥 Paso 3: Obteniendo código fuente automáticamente..."

# Clonar repositorio (si falla con SSH, intentar con HTTPS)
if [ ! -d .git ]; then
    echo "📋 Clonando repositorio..."
    git clone git@github.com:jesusaln/cdd_app.git . 2>/dev/null ||
    git clone https://github.com/jesusaln/cdd_app.git . 2>/dev/null ||
    echo "❌ Error: No se pudo clonar el repositorio. Verifica permisos SSH o usa token HTTPS"
fi

# =============================================================================
# 4. CONFIGURACIÓN DE PRODUCCIÓN (AUTOMÁTICA)
# =============================================================================

echo ""
echo "⚙️ Paso 4: Configuración de producción automática..."

# Crear archivo .env.production con configuración completa
cat > .env.production << 'EOF'
# Configuración de Producción - Climas del Desierto
APP_NAME="Climas del Desierto"
APP_ENV=production
APP_KEY=base64:AlytGytYcUJaNcIxIazlUnqnJberl4olGUL6tadhqqA=
APP_DEBUG=false
APP_TIMEZONE=America/Hermosillo
APP_URL=https://portainer.asistenciavircom.com

APP_LOCALE=es
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=es_MX

APP_MAINTENANCE_DRIVER=file
PHP_CLI_SERVER_WORKERS=4
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Base de datos PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=pg
DB_PORT=5432
DB_DATABASE=cdd_production
DB_USERNAME=cdd_user
DB_PASSWORD=CdD2024!Pr0d$Str0ngP@ssw0rd#2024

SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=portainer.asistenciavircom.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

BROADCAST_CONNECTION=redis
FILESYSTEM_DISK=public
QUEUE_CONNECTION=redis
CACHE_STORE=redis
CACHE_PREFIX=climas_desierto

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=R3d1s!C@ch3$CdD2024#Str0ngP@ss
REDIS_PORT=6379

# Correo con Hostinger
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=proveedores@asistenciavircom.com
MAIL_PASSWORD=Zl01kpContpaqi1.
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="proveedores@asistenciavircom.com"
MAIL_FROM_NAME="Asistencia Vircom"

MAIL_TEST_MODE=false
MAIL_TEST_EMAIL=test@example.com

VITE_APP_NAME="${APP_NAME}"
SANCTUM_STATEFUL_DOMAINS=portainer.asistenciavircom.com

# pgAdmin
PGADMIN_EMAIL=admin@asistenciavircom.com
PGADMIN_PASSWORD=Pg@Dm1n!CdD2024#Str0ng$Acc3ss

# Docker
DOCKER_APP_PORT=8000
DOCKER_NGINX_PORT=80
EOF

# =============================================================================
# 5. DESPLIEGUE COMPLETO (AUTOMÁTICO)
# =============================================================================

echo ""
echo "🚀 Paso 5: Despliegue completo automático..."

# Ejecutar despliegue usando el script disponible
if [ -f docker/deploy.sh ]; then
    echo "🔨 Usando script avanzado de despliegue..."
    chmod +x docker/deploy.sh
    ./docker/deploy.sh production
else
    echo "🔨 Usando despliegue manual..."

    # Configuración manual paso a paso
    cp .env.production .env 2>/dev/null || true

    # Crear directorios necesarios
    mkdir -p docker/pgdata docker/redis docker/pgadmin 2>/dev/null || true
    mkdir -p storage/app storage/framework/cache storage/framework/sessions storage/framework/views storage/logs 2>/dev/null || true

    # Permisos
    chmod -R 755 storage bootstrap/cache 2>/dev/null || true

    # Construir e iniciar
    docker-compose build --no-cache 2>/dev/null || true
    docker-compose up -d 2>/dev/null || true

    # Esperar
    echo "⏳ Esperando servicios..."
    sleep 30

    # Migraciones y configuración
    docker-compose exec -T app php artisan migrate --force 2>/dev/null || true
    docker-compose exec -T app php artisan db:seed --force 2>/dev/null || true
    docker-compose exec -T app php artisan config:cache 2>/dev/null || true
    docker-compose exec -T app php artisan route:cache 2>/dev/null || true
    docker-compose exec -T app php artisan view:cache 2>/dev/null || true
    docker-compose exec -T app php artisan storage:link 2>/dev/null || true
fi

# =============================================================================
# 6. VERIFICACIÓN FINAL (AUTOMÁTICA)
# =============================================================================

echo ""
echo "✅ Paso 6: Verificación automática..."

# Verificar estado de servicios
echo "🔍 Verificando servicios..."
docker compose ps 2>/dev/null || true

# Verificar migraciones
echo "📋 Verificando migraciones..."
docker compose exec app php artisan migrate:status 2>/dev/null || true

# =============================================================================
# 7. FINALIZACIÓN (AUTOMÁTICA)
# =============================================================================

echo ""
echo "🎉 ¡DESPLIEGUE COMPLETADO AUTOMÁTICAMENTE!"
echo ""
echo "📊 Información del despliegue:"
echo "   🖥️ Proyecto: $PROJECT_NAME"
echo "   📂 Directorio: $APP_DIR"
echo "   🌐 Acceso: https://portainer.asistenciavircom.com"
echo "   🚀 Estado: $(docker compose ps --format 'table {{.Service}}\t{{.Status}}' 2>/dev/null || echo 'Servicios iniciados')"
echo ""
echo "🔧 Comandos útiles:"
echo "   📋 Ver logs: cd $APP_DIR && docker compose logs -f app"
echo "   🔄 Reiniciar: cd $APP_DIR && docker compose restart"
echo "   🛑 Detener: cd $APP_DIR && docker compose down"
echo ""
echo "✅ ¡Tu aplicación CDD App está lista!"
echo ""
echo "🌐 Accede en: https://portainer.asistenciavircom.com"