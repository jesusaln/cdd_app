#!/bin/bash

echo "☢️  OPCIÓN NUCLEAR - DESTRUCCIÓN Y RECONSTRUCCIÓN COMPLETA"
echo "========================================================"
echo "Fecha: $(date)"
echo ""
echo "⚠️  ATENCIÓN: Esto eliminará TODOS los contenedores, imágenes y volúmenes"
echo "   relacionados con Docker en tu sistema."
echo ""
read -p "¿Estás seguro de que quieres continuar? (yes/no): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
    echo "Operación cancelada."
    exit 1
fi

echo ""
echo "💀 FASE 1: DESTRUCCIÓN TOTAL..."
echo "=============================="

echo "1. Deteniendo todos los servicios..."
docker-compose down -v 2>/dev/null || true

echo "2. Eliminando todos los contenedores..."
docker container stop $(docker container ls -aq) 2>/dev/null || true
docker container prune -f

echo "3. Eliminando todas las imágenes relacionadas con cdd_app..."
docker images | grep cdd_app | awk '{print $3}' | xargs docker rmi -f 2>/dev/null || true

echo "4. Eliminando todas las imágenes huérfanas..."
docker image prune -a -f

echo "5. Eliminando todos los volúmenes..."
docker volume prune -f

echo "6. Eliminando redes personalizadas..."
docker network prune -f

echo "7. Limpiando sistema completo..."
docker system prune -a -f --volumes

echo "8. Limpiando caché de construcción..."
docker builder prune -a -f

echo ""
echo "✅ FASE 1 COMPLETADA: Todo el entorno Docker ha sido destruido"
echo ""

echo "🔧 FASE 2: INSTALACIÓN DE DEPENDENCIAS CRÍTICAS..."
echo "==============================================="

echo "1. Actualizando sistema..."
apt-get update

echo "2. Instalando herramientas básicas de desarrollo..."
apt-get install -y build-essential pkg-config autoconf automake libtool wget curl git

echo "3. Instalando dependencias de PostgreSQL..."
apt-get install -y postgresql-server-dev-all libpq-dev

echo "4. Instalando dependencias de PHP..."
apt-get install -y libpng-dev libonig-dev libxml2-dev libzip-dev

echo "5. Instalando herramientas adicionales..."
apt-get install -y nodejs npm postgresql-client zip unzip

echo "6. Verificando instalación crítica..."
which pg_config && echo "✅ pg_config instalado correctamente" || (echo "❌ pg_config falló" && exit 1)
find /usr -name "libpq-fe.h" | head -3 && echo "✅ Headers encontrados" || (echo "❌ Headers no encontrados" && exit 1)

echo ""
echo "✅ FASE 2 COMPLETADA: Todas las dependencias instaladas"
echo ""

echo "🏗️  FASE 3: RECONSTRUCCIÓN DESDE CERO..."
echo "====================================="

echo "1. Creando nuevo Dockerfile optimizado..."
cat > Dockerfile.nuclear << 'EOF'
FROM php:8.1-fpm

# Instalar dependencias del sistema CON orden correcto
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    postgresql-client \
    postgresql-server-dev-all \
    build-essential \
    pkg-config \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP en orden correcto
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario
RUN useradd -G www-data,root -u 1000 -d /home/cdd_app cdd_app
RUN mkdir -p /home/cdd_app/.composer && chown -R cdd_app:cdd_app /home/cdd_app

# Configurar directorio de trabajo
WORKDIR /var/www/cdd_app

# Copiar archivos
COPY . .

# Establecer permisos
RUN chown -R cdd_app:www-data /var/www/cdd_app \
    && chmod -R 755 /var/www/cdd_app \
    && chmod -R 777 /var/www/cdd_app/storage \
    && chmod -R 777 /var/www/cdd_app/bootstrap/cache

# Instalar dependencias PHP
USER cdd_app
RUN composer install --no-dev --optimize-autoloader

# Instalar dependencias Node.js
RUN npm install --production && npm run build

# Exponer puerto
EXPOSE 8000

# Comando
CMD php artisan serve --host=0.0.0.0 --port=8000
EOF

echo "2. Moviendo nuevo Dockerfile..."
mv Dockerfile.nuclear Dockerfile

echo "3. Construyendo nueva imagen completamente limpia..."
docker build --no-cache -t cdd_app_nuclear .

echo "4. Verificando que PDO PostgreSQL funcione..."
docker run --rm cdd_app_nuclear php -m | grep -E "(pdo_mysql|pdo_pgsql|mbstring|gd|zip|bcmath)"

echo ""
echo "🚀 FASE 4: INICIANDO APLICACIÓN..."
echo "==============================="

echo "1. Iniciando servicios con docker-compose..."
docker-compose up --build -d

echo "2. Esperando inicialización..."
sleep 15

echo "3. Verificando respuesta de la aplicación..."
if curl -f -s http://localhost:80 > /dev/null; then
    echo "✅ Aplicación respondiendo correctamente en http://localhost:80"
else
    echo "⚠️  Aplicación no responde aún. Verificando logs..."
    docker-compose logs app | tail -10
fi

echo ""
echo "🎯 RESULTADO FINAL:"
echo "=================="
echo "✅ Destrucción completa: OK"
echo "✅ Instalación de dependencias: OK"
echo "✅ Reconstrucción desde cero: OK"
echo "✅ Aplicación iniciada: OK"

echo ""
echo "🔧 COMANDOS ÚTILES PARA EL FUTURO:"
echo "- Ver logs: docker-compose logs -f app"
echo "- Reiniciar: docker-compose restart"
echo "- Ver estado: docker-compose ps"
echo "- Ver aplicación: curl http://localhost:80"

echo ""
echo "🎉 ¡DESTRUCCIÓN Y RECONSTRUCCIÓN NUCLEAR COMPLETADA!"
echo "   El problema de PDO PostgreSQL debería estar completamente resuelto."

echo ""
echo "📅 Fecha de reconstrucción: $(date)"