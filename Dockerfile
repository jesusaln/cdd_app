# ===== Stage 0: variables y hints =====
# Habilita BuildKit: DOCKER_BUILDKIT=1 docker build ...
# Usa --build-arg APP_ENV=production para cachear mejor
ARG APP_ENV=production

# ===== Stage 1: Composer (con cache) =====
FROM composer:2 AS vendor
WORKDIR /app
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    COMPOSER_CACHE_DIR=/tmp/composer-cache

# solo estos archivos invalidan la capa de vendor
COPY composer.json composer.lock ./

# cache mount acelera composer install
RUN --mount=type=cache,target=/tmp/composer-cache \
    composer install --no-dev --prefer-dist --no-progress --no-scripts

# ahora sí copia el resto
COPY . .

# vuelve a instalar por si apareció algún paquete extra (suele ser no-op)
RUN --mount=type=cache,target=/tmp/composer-cache \
    composer install --no-dev --prefer-dist --no-progress --optimize-autoloader

# ===== Stage 2: Node para assets (opcional si usas Vite/Laravel Mix) =====
FROM node:20-slim AS assets
WORKDIR /app
COPY package.json package-lock.json* pnpm-lock.yaml* yarn.lock* .npmrc* ./
RUN --mount=type=cache,target=/root/.npm \
    if [ -f package-lock.json ]; then npm ci; \
    elif [ -f pnpm-lock.yaml ]; then npm i -g pnpm && pnpm i --frozen-lockfile; \
    elif [ -f yarn.lock ]; then npm i -g yarn && yarn install --frozen-lockfile; \
    else echo "No lockfile. Skipping."; fi
COPY . .
# Ajusta al comando real de tu proyecto (vite build / mix / etc.)
RUN if [ -f package.json ]; then npm run build || true; fi

# ===== Stage 3: Runtime PHP 8.3 + Apache =====
FROM php:8.3-apache

# Extensiones necesarias para Laravel + Excel
RUN apt-get update && apt-get install -y --no-install-recommends \
      libpq-dev libzip-dev unzip git \
      libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
  && docker-php-ext-install pdo pdo_pgsql zip \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install gd \
  && pecl install redis \
  && docker-php-ext-enable redis \
  && a2enmod rewrite \
  && rm -rf /var/lib/apt/lists/*

# Sirve /public
RUN sed -i 's#/var/www/html#/var/www/html/public#' /etc/apache2/sites-available/000-default.conf
WORKDIR /var/www/html

# Copia app + vendor desde Stage 1
COPY --from=vendor /app /var/www/html

# Copia assets construidos (si existen)
COPY --from=assets /app/public/build /var/www/html/public/build

# Permisos mínimos
RUN chown -R www-data:www-data storage bootstrap/cache

# (Opcional) Precalentar cachés de Laravel en build usando .env de ejemplo
# Evita tiempos extra en primer arranque (ajusta si no quieres esto en build)
ARG APP_ENV
ENV APP_ENV=${APP_ENV}
RUN php -v && php -m \
 && rm -f .env || true

EXPOSE 80