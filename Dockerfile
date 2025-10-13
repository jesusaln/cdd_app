# ---------- Stage 1: Assets (Vite) ----------
FROM node:20-alpine AS assets
WORKDIR /app

# Dependencias nativas para algunos paquetes npm
RUN apk add --no-cache python3 make g++

# Copia definiciones y lock (ajusta según uses npm/pnpm/yarn)
COPY package*.json ./
RUN npm ci

# Copia solo lo necesario para construir front
COPY vite.config.* ./
COPY resources ./resources
COPY public ./public

# Compilar assets → /public/build
RUN npm run build


# ---------- Stage 2: Composer ----------
FROM composer:2 AS composer_deps
WORKDIR /app

# Configurar entorno para Composer
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    COMPOSER_CACHE_DIR=/tmp/composer-cache

# Instalar dependencias del sistema necesarias para algunos paquetes PHP
RUN apk add --no-cache git unzip libzip-dev zlib-dev libxml2-dev curl-dev

# Copia composer.* y resuelve dependencias (sin dev, con autoloader optimizado)
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache \
    composer install --no-dev --prefer-dist --no-progress --no-interaction --optimize-autoloader --no-scripts \
    --ignore-platform-req=ext-gd --ignore-platform-req=ext-zip --ignore-platform-req=ext-curl

# Si tu app necesita los archivos para el post-autoload-dump, copia mínimo app/ y demás
# (opcional; composer 2 suele bastar con composer.json/lock)
# COPY app ./app
# RUN composer dump-autoload --no-dev --classmap-authoritative


# ---------- Stage 3: App (PHP 8.3 + Apache) ----------
FROM php:8.3-apache
WORKDIR /var/www/html

# Instalar dependencias del sistema necesarias
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev libzip-dev unzip git zlib1g-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev \
    libxml2-dev libcurl4-openssl-dev libssl-dev \
    libonig-dev libargon2-0-dev \
    $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones básicas una por una (orden correcto)
RUN docker-php-ext-install pdo
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install xml
RUN docker-php-ext-install curl
RUN docker-php-ext-install opcache

# Instalar extensiones que requieren configuración especial
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install pgsql
RUN docker-php-ext-install zip

# Configurar e instalar GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd

# Redis via PECL
RUN pecl install redis \
 && docker-php-ext-enable redis

# Apache modules
RUN a2enmod rewrite headers expires

# OPcache para producción
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=0'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# DocumentRoot → /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
            -e 's!Directory /var/www/!Directory ${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf || true

# Copia el código de la app (sin sobrescribir public todavía)
COPY . .

# Borra public para reemplazarlo por el compilado
RUN rm -rf /var/www/html/public

# Copia assets compilados y .htaccess desde Stage assets
COPY --from=assets /app/public /var/www/html/public

# Copia vendor desde Stage composer
COPY --from=composer_deps /app/vendor /var/www/html/vendor

# Directorios necesarios y permisos
RUN mkdir -p storage/framework/{cache/data,sessions,views} storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache public \
 && chmod -R ug+rwX storage bootstrap/cache

EXPOSE 80
