# Etapa 1: Node para compilar Vite
FROM node:20-alpine AS assets
WORKDIR /app

# Instalar dependencias del sistema necesarias para algunos paquetes de npm
RUN apk add --no-cache python3 make g++

COPY package.json package-lock.json* pnpm-lock.yaml* yarn.lock* ./
# elige tu gestor: npm ci / pnpm i / yarn install
RUN npm ci

# Copiar archivos necesarios para el build
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

# Crear directorio build y construir assets
RUN mkdir -p public/build && npm run build

# Etapa 2: PHP + Apache (o FPM)
FROM php:8.3-apache
WORKDIR /var/www/html

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev libzip-dev unzip git \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev \
    libxml2-dev libcurl4-openssl-dev libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP necesarias (una por una para mejor debugging)
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install pgsql
RUN docker-php-ext-install zip
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install xml
RUN docker-php-ext-install curl
RUN docker-php-ext-install opcache

# Instalar GD con soporte completo
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd

# Instalar Redis
RUN pecl install redis \
    && docker-php-ext-enable redis

# Habilitar módulos de Apache
RUN a2enmod rewrite headers expires

# Configurar OPcache para producción
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=0'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Configurar Apache para usar /public como DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf

# Crear directorios necesarios
RUN mkdir -p /var/www/html/storage/app /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views \
    /var/www/html/storage/logs /var/www/html/bootstrap/cache

# Copiar aplicación primero (sin sobrescribir public)
COPY . .
# Remover el directorio public para evitar conflictos
RUN rm -rf /var/www/html/public

# Copiar el directorio public desde assets (que incluye .htaccess y assets compilados)
COPY --from=assets /app/public /var/www/html/public

# Verificar que archivos importantes existen
RUN ls -la /var/www/html/public/ && test -f /var/www/html/public/.htaccess && test -f /var/www/html/public/index.php

# permisos correctos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && find /var/www/html/public -type f -name "*.php" -exec chmod 644 {} \; \
    && find /var/www/html/public -type f -name ".htaccess" -exec chmod 644 {} \;

EXPOSE 80
