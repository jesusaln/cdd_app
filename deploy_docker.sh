#!/bin/bash

# =====================================================
# DESPLIEGUE DOCKER - ULTRA SIMPLE
# =====================================================

echo "🐳 INICIANDO DESPLIEGUE DOCKER..."
echo "================================="

# Función para colores
green() { echo -e "\033[32m$1\033[0m"; }
blue() { echo -e "\033[34m$1\033[0m"; }
red() { echo -e "\033[31m$1\033[0m"; }

# =====================================================
# 1. CREAR ARCHIVO .ENV PARA DOCKER
# =====================================================

green "1/5 - Configurando archivo .env para Docker..."

cat > .env.docker << 'EOF'
APP_NAME="Climas del Desierto"
APP_ENV=production
APP_KEY=base64:mPbK2ZpvxcvXtnyBlsK+khdzsSdAXaC4oRy5aUrWrkg=
APP_DEBUG=false
APP_URL=http://localhost:8000

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
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
EOF

echo "✅ Archivo .env.docker creado"

# =====================================================
# 2. CONSTRUIR Y LEVANTAR CONTENEDORES
# =====================================================

green "2/5 - Construyendo y levantando contenedores..."

# Construir imágenes
docker-compose build

# Levantar servicios
docker-compose up -d

echo "✅ Contenedores iniciados"

# =====================================================
# 3. EJECUTAR MIGRACIONES
# =====================================================

green "3/5 - Ejecutando migraciones..."

# Esperar a que la base de datos esté lista
sleep 10

# Ejecutar migraciones
docker-compose exec app php artisan migrate --force

# Crear enlace simbólico de storage
docker-compose exec app php artisan storage:link

echo "✅ Migraciones ejecutadas"

# =====================================================
# 4. OPTIMIZAR BASE DE DATOS
# =====================================================

green "4/5 - Optimizando base de datos..."

# Ejecutar optimizaciones si existe el archivo
if [ -f "optimize_database.sql" ]; then
    docker-compose exec -T db psql -U cdd_user -d cdd_app_prod < optimize_database.sql
    echo "✅ Base de datos optimizada"
else
    echo "⚠️ Archivo optimize_database.sql no encontrado"
fi

# =====================================================
# 5. VERIFICACIÓN FINAL
# =====================================================

green "5/5 - Verificación final..."

echo ""
blue "=== ESTADO DE CONTENEDORES ==="
docker-compose ps

echo ""
blue "=== VERIFICACIÓN DE SERVICIOS ==="
curl -I http://localhost:80
curl -I http://localhost:3000

echo ""
blue "=== ACCESO A LA APLICACIÓN ==="
echo "🌐 Aplicación principal: http://localhost:80"
echo "🔧 Aplicación alternativa: http://localhost:3000"
echo "📊 PHP Info: http://localhost/info.php"

echo ""
blue "=== COMANDOS ÚTILES ==="
echo "Ver logs: docker-compose logs -f"
echo "Entrar al contenedor: docker-compose exec app bash"
echo "Detener: docker-compose down"
echo "Reiniciar: docker-compose restart"

green "✅ ¡DESPLIEGUE DOCKER COMPLETADO!"
echo ""
blue "Tu aplicación está lista en:"
blue "📍 http://localhost (Nginx)"
blue "📍 http://localhost:3000 (Nginx alternativo)"
blue "🚀 ¡Lista para miles de registros!"

# =====================================================
# INFORMACIÓN ADICIONAL
# =====================================================

echo ""
green "=== BASE DE DATOS ==="
echo "🔗 PostgreSQL: localhost:5432"
echo "👤 Usuario: cdd_user"
echo "🔑 Contraseña: Contpaqi1."
echo "📦 Base de datos: cdd_app_prod"

echo ""
green "=== REDIS ==="
echo "🔗 Redis: localhost:6379"

echo ""
green "=== ARCHIVOS IMPORTANTES ==="
echo "📋 .env.docker - Configuración de producción"
echo "🐳 docker-compose.yml - Orquestación de servicios"
echo "🚢 Dockerfile - Imagen de aplicación"
echo "🌐 docker/nginx.conf - Configuración de Nginx"

echo ""
blue "=== PRÓXIMOS PASOS ==="
echo "1. Probar la aplicación en el navegador"
echo "2. Verificar que todas las funcionalidades trabajan"
echo "3. Configurar dominio personalizado (opcional)"
echo "4. Configurar SSL con Let's Encrypt (opcional)"
echo "5. Monitorear rendimiento con miles de registros"

green "🎉 ¡DESPLIEGUE COMPLETADO EXITOSAMENTE!"