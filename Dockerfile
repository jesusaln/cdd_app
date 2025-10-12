# ===== Stage 0: variables =====
ARG APP_ENV=production
ARG BUILD_ASSETS=true   # pon "false" si no construyes assets

# ===== Stage 1: Composer (con cache) =====
FROM composer:2 AS vendor
WORKDIR /app
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    COMPOSER_CACHE_DIR=/tmp/composer-cache

# Copia solo lo que invalida la capa de dependencias
COPY composer.json composer.lock ./

# Si algún paquete pide ext-gd, lo ignoramos en build (en runtime sí estará)
RUN --mount=type=cache,target=/tmp/composer-cache \
    composer install --no-dev --prefer-dist --no-progress --no-scripts \
    --ignore-platform-req=ext-gd

# Ahora sí copia el resto del proyecto
COPY . .

# Segunda pasada (normalmente no-op si no cambió nada extra)
RUN --mount=type=cache,target=/tmp/composer-cache \
    composer install --no-dev --prefer-dist --no-progress --optimize-autoloader \
    --ignore-platform-req=ext-gd

# ===== Stage 2: Node (opcional) =====
FROM node:20-slim AS assets
ARG BUILD_ASSETS
WORKDIR /app

# Copiamos TODO primero (evita fallos si no existe package.json)
COPY . .

# Siempre asegura el directorio para que el COPY no falle
RUN mkdir -p public/build && \
    if [ "$BUILD_ASSETS" = "true" ] && [ -f package.json ]; then \
      if [ -f package-lock.json ]; then \
        --mount=type=cache,target=/root/.npm npm ci; \
      else \
        --mount=type=cache,target=/root/.npm npm install --no-audit --no-fund; \
      fi && \
      npm run build || true; \
    else \
      echo "Omitiendo build de assets"; \
    fi

# ===== Stage 3: Runtime PHP 8.3 + Apache =====
FROM php:8.3-apache

# Extensiones necesarias (pgsql, zip, gd) + redis + performance
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev libzip-dev unzip git \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && a2enmod rewrite headers expires \
    && rm -rf /var/lib/apt/lists/*

# Opcache para prod
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=0'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# DocumentRoot -> /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Copiar app (con vendor de la Stage 1)
COPY --from=vendor /app /var/www/html

# Copiar assets si existen
COPY --from=assets /app/public/build /var/www/html/public/build

# Permisos mínimos
RUN chown -R www-data:www-data storage bootstrap/cache

# Ajustes de entorno
ARG APP_ENV
ENV APP_ENV=${APP_ENV}

EXPOSE 80
