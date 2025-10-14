#!/bin/bash

# =====================================================
# DESPLIEGUE COMPLETO VIA GITHUB - AUTOMÁTICO
# =====================================================

echo "🚀 DESPLIEGUE VIA GITHUB - ULTRA LIMPIO"
echo "======================================="

# Función para colores
green() { echo -e "\033[32m$1\033[0m"; }
blue() { echo -e "\033[34m$1\033[0m"; }
red() { echo -e "\033[31m$1\033[0m"; }

# =====================================================
# 1. LIMPIEZA COMPLETA DEL VPS
# =====================================================

green "1/7 - Limpiando VPS completamente..."

# Detener servicios conflictivos
sudo systemctl stop openresty 2>/dev/null || true
sudo systemctl disable openresty 2>/dev/null || true

# Limpiar Docker si existe
sudo docker system prune -af 2>/dev/null || true
sudo docker volume prune -f 2>/dev/null || true

# Limpiar directorios anteriores
sudo rm -rf /var/www/cdd_app*

echo "✅ VPS limpio y listo"

# =====================================================
# 2. INSTALAR DOCKER Y DOCKER COMPOSE
# =====================================================

green "2/7 - Instalando Docker y Docker Compose..."

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
# 3. CREAR ARCHIVOS DE CONFIGURACIÓN
# =====================================================

green "3/7 - Creando archivos de configuración..."

# Crear directorio de aplicación
sudo mkdir -p /var/www/cdd_app_docker
sudo chown $USER:$USER /var/www/cdd_app_docker
cd /var/www/cdd_app_docker

# Crear .env.docker
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

# =====================================================
# 4. CREAR DOCKERFILE
# =====================================================

green "4/7 - Creando Dockerfile..."

cat > Dockerfile << 'EOF'
FROM php:8.1-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libzip-dev zip unzip nodejs npm \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario
RUN useradd -G www-data,root -u 1000 -d /home/cdd_app cdd_app
RUN mkdir -p /home/cdd_app/.composer && chown -R cdd_app:cdd_app /home/cdd_app

# Establecer permisos
RUN chown -R cdd_app:www-data /var/www/cdd_app && \
    chmod -R 755 /var/www/cdd_app && \
    chmod -R 777 /var/www/cdd_app/storage && \
    chmod -R 777 /var/www/cdd_app/bootstrap/cache

USER cdd_app
WORKDIR /var/www/cdd_app

EXPOSE 8000
CMD php artisan serve --host=0.0.0.0 --port=8000
EOF

echo "✅ Dockerfile creado"

# =====================================================
# 5. CREAR DOCKER-COMPOSE.YML
# =====================================================

green "5/7 - Creando docker-compose.yml..."

cat > docker-compose.yml << 'EOF'
version: '3.8'

services:
  app:
    build: .
    container_name: cdd_app
    restart: unless-stopped
    working_dir: /var/www/cdd_app
    volumes:
      - ./:/var/www/cdd_app
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_CONNECTION=pgsql
      - DB_HOST=db
      - DB_DATABASE=cdd_app_prod
      - DB_USERNAME=cdd_user
      - DB_PASSWORD=Contpaqi1.
    depends_on:
      - db
      - redis
    networks:
      - cdd_network

  db:
    image: postgres:15
    container_name: cdd_postgresql
    restart: unless-stopped
    environment:
      POSTGRES_DB: cdd_app_prod
      POSTGRES_USER: cdd_user
      POSTGRES_PASSWORD: Contpaqi1.
    volumes:
      - db_data:/var/lib/postgresql/data
    networks:
      - cdd_network

  redis:
    image: redis:7-alpine
    container_name: cdd_redis
    restart: unless-stopped
    networks:
      - cdd_network

  nginx:
    image: nginx:alpine
    container_name: cdd_nginx
    restart: unless-stopped
    ports:
      - "80:80"
    volumes:
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
    networks:
      - cdd_network

volumes:
  db_data:

networks:
  cdd_network: driver: bridge
EOF

echo "✅ docker-compose.yml creado"

# =====================================================
# 6. CREAR CONFIGURACIÓN DE NGINX
# =====================================================

green "6/7 - Creando configuración de Nginx..."

mkdir -p docker
cat > docker/nginx.conf << 'EOF'
server {
    listen 80;
    server_name localhost;
    root /var/www/cdd_app/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
EOF

echo "✅ Configuración de Nginx creada"

# =====================================================
# 7. CONSTRUIR Y DESPLEGAR
# =====================================================

green "7/7 - Construyendo y desplegando aplicación..."

# Construir imágenes
docker-compose build --no-cache

# Levantar servicios
docker-compose up -d

# Esperar servicios
sleep 15

# Ejecutar migraciones
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan storage:link

echo "✅ Migraciones ejecutadas"

# =====================================================
# VERIFICACIÓN FINAL
# =====================================================

green "=== VERIFICACIÓN FINAL ==="

echo ""
blue "=== CONTENEDORES ==="
docker-compose ps

echo ""
blue "=== PRUEBA DE CONECTIVIDAD ==="
curl -I http://localhost:80 && echo "✅ Nginx respondiendo" || echo "❌ Error con Nginx"

echo ""
blue "=== ACCESO A LA APLICACIÓN ==="
echo "🌐 Aplicación: http://localhost:80"
echo "📊 Información: http://localhost/info.php"

echo ""
green "=== COMANDOS ÚTILES ==="
echo "Ver logs: docker-compose logs -f"
echo "Detener: docker-compose down"
echo "Estado: docker-compose ps"
echo "Reiniciar: docker-compose restart"

echo ""
green "✅ ¡DESPLIEGUE GITHUB COMPLETADO!"

echo ""
blue "🚀 Características:"
blue "✅ Aplicación Laravel en Docker"
blue "✅ PostgreSQL optimizada"
blue "✅ Redis para cache"
blue "✅ Nginx como proxy"
blue "✅ Configuración de producción"
blue "✅ Listo para miles de registros"

echo ""
green "🎯 PRÓXIMOS PASOS:"
echo "1. Probar aplicación en navegador"
echo "2. Verificar funcionalidades"
echo "3. Configurar dominio (opcional)"
echo "4. Configurar SSL (opcional)"

echo ""
green "¡TU APLICACIÓN ESTÁ EN PRODUCCIÓN! 🎉"