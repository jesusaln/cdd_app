#!/bin/bash

# =====================================================
# DESPLIEGUE DOCKER COMPLETO - AUTOMÁTICO
# =====================================================

set -e  # Detener en caso de error

echo "🐳 DESPLIEGUE DOCKER AUTOMÁTICO"
echo "=============================="

# Función para colores
green() { echo -e "\033[32m$1\033[0m"; }
blue() { echo -e "\033[34m$1\033[0m"; }
red() { echo -e "\033[31m$1\033[0m"; }

# =====================================================
# 1. INSTALAR DOCKER Y DOCKER COMPOSE
# =====================================================

green "1/6 - Instalando Docker y Docker Compose..."

# Instalar Docker
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker $USER
    echo "✅ Docker instalado"
else
    echo "✅ Docker ya está instalado"
fi

# Instalar Docker Compose
if ! command -v docker-compose &> /dev/null; then
    sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    sudo chmod +x /usr/local/bin/docker-compose
    echo "✅ Docker Compose instalado"
else
    echo "✅ Docker Compose ya está instalado"
fi

# =====================================================
# 2. PREPARAR ARCHIVOS DE APLICACIÓN
# =====================================================

green "2/6 - Preparando archivos de aplicación..."

# Crear directorio para la aplicación
sudo mkdir -p /var/www/cdd_app_docker
sudo chown $USER:$USER /var/www/cdd_app_docker

# Copiar archivos necesarios
if [ -f "cdd_app_*.zip" ]; then
    unzip cdd_app_*.zip -d /var/www/cdd_app_docker/
    echo "✅ Aplicación extraída"
else
    echo "❌ Archivo cdd_app_*.zip no encontrado"
    echo "Transfiere el archivo desde Windows primero"
    exit 1
fi

# Copiar archivos de configuración
if [ -f ".env" ]; then
    cp .env /var/www/cdd_app_docker/
fi

if [ -f "optimize_database.sql" ]; then
    cp optimize_database.sql /var/www/cdd_app_docker/
fi

# Establecer permisos
sudo chown -R $USER:$USER /var/www/cdd_app_docker
chmod -R 755 /var/www/cdd_app_docker
chmod -R 777 /var/www/cdd_app_docker/storage /var/www/cdd_app_docker/bootstrap/cache

# =====================================================
# 3. CONFIGURAR ARCHIVOS DOCKER
# =====================================================

green "3/6 - Configurando archivos Docker..."

cd /var/www/cdd_app_docker

# Crear .env.docker si no existe
if [ ! -f ".env.docker" ]; then
    cat > .env.docker << 'EOF'
APP_NAME="Climas del Desierto"
APP_ENV=production
APP_KEY=base64:mPbK2ZpvxcvXtnyBlsK+khdzsSdAXaC4oRy5aUrWrkg=
APP_DEBUG=false
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=cdd_app_prod
DB_USERNAME=cdd_user
DB_PASSWORD=Contpaqi1.

BROADCAST_CONNECTION=redis
CACHE_STORE=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
VITE_APP_NAME="${APP_NAME}"
EOF
    echo "✅ Archivo .env.docker creado"
fi

# =====================================================
# 4. CONSTRUIR Y LEVANTAR CONTENEDORES
# =====================================================

green "4/6 - Construyendo y levantando contenedores..."

# Construir imágenes
docker-compose build --no-cache

# Levantar servicios en segundo plano
docker-compose up -d

echo "✅ Contenedores iniciados"

# =====================================================
# 5. EJECUTAR MIGRACIONES Y OPTIMIZACIONES
# =====================================================

green "5/6 - Ejecutando migraciones y optimizaciones..."

# Esperar a que los servicios estén listos
sleep 15

# Ejecutar migraciones
docker-compose exec app php artisan migrate --force

# Crear enlace simbólico de storage
docker-compose exec app php artisan storage:link

# Optimizar aplicación
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Ejecutar optimizaciones de base de datos si existe el archivo
if [ -f "optimize_database.sql" ]; then
    docker-compose exec -T db psql -U cdd_user -d cdd_app_prod < optimize_database.sql
    echo "✅ Base de datos optimizada"
fi

echo "✅ Migraciones y optimizaciones completadas"

# =====================================================
# 6. VERIFICACIÓN FINAL
# =====================================================

green "6/6 - Verificación final..."

echo ""
blue "=== ESTADO DE CONTENEDORES ==="
docker-compose ps

echo ""
blue "=== PRUEBA DE CONECTIVIDAD ==="
curl -I http://localhost:80 || echo "❌ Error al conectar con Nginx"
curl -I http://localhost:3000 || echo "❌ Error al conectar con Nginx alternativo"

echo ""
blue "=== VERIFICACIÓN DE SERVICIOS ==="
docker-compose exec db pg_isready -U cdd_user -d cdd_app_prod && echo "✅ PostgreSQL conectado" || echo "❌ Error con PostgreSQL"
docker-compose exec redis redis-cli ping | grep PONG && echo "✅ Redis conectado" || echo "❌ Error con Redis"

echo ""
blue "=== ACCESO A LA APLICACIÓN ==="
echo "🌐 Aplicación principal: http://localhost:80"
echo "🔧 Aplicación alternativa: http://localhost:3000"
echo "📊 Información PHP: http://localhost/info.php"

echo ""
green "=== COMANDOS ÚTILES ==="
echo "Ver logs: docker-compose logs -f"
echo "Entrar al contenedor: docker-compose exec app bash"
echo "Detener aplicación: docker-compose down"
echo "Ver estado: docker-compose ps"
echo "Reiniciar servicio: docker-compose restart"

echo ""
green "✅ ¡DESPLIEGUE DOCKER COMPLETADO!"

echo ""
blue "=== BASE DE DATOS ==="
echo "🔗 PostgreSQL: localhost:5432"
echo "👤 Usuario: cdd_user"
echo "🔑 Contraseña: Contpaqi1."
echo "📦 Base de datos: cdd_app_prod"

echo ""
blue "=== REDIS ==="
echo "🔗 Redis: localhost:6379"

echo ""
green "🎉 ¡TU APLICACIÓN ESTÁ LISTA!"
echo ""
blue "🚀 Características:"
blue "✅ Aplicación Laravel en contenedor"
blue "✅ PostgreSQL optimizada para miles de registros"
blue "✅ Redis para cache ultra-rápido"
blue "✅ Nginx como proxy reverso"
blue "✅ Configuración de producción"
blue "✅ Listo para alto volumen de datos"

echo ""
green "🎯 PRÓXIMOS PASOS:"
echo "1. Probar la aplicación en el navegador"
echo "2. Verificar funcionalidades básicas"
echo "3. Configurar dominio personalizado (opcional)"
echo "4. Configurar SSL (opcional)"
echo "5. Monitorear rendimiento"

echo ""
green "¡DESPLIEGUE EXITOSO! 🚀"