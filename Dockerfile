FROM php:8.1-fpm

# Instalar dependencias del sistema incluyendo PostgreSQL dev
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
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario para la aplicación
RUN useradd -G www-data,root -u 1000 -d /home/cdd_app cdd_app
RUN mkdir -p /home/cdd_app/.composer && chown -R cdd_app:cdd_app /home/cdd_app

# Establecer directorio de trabajo
WORKDIR /var/www/cdd_app

# Copiar archivos de la aplicación
COPY . .

# Establecer permisos
RUN chown -R cdd_app:www-data /var/www/cdd_app \
    && chmod -R 755 /var/www/cdd_app \
    && chmod -R 777 /var/www/cdd_app/storage \
    && chmod -R 777 /var/www/cdd_app/bootstrap/cache

# Instalar dependencias PHP
USER cdd_app
RUN composer install --no-dev --optimize-autoloader

# Instalar dependencias Node.js y compilar assets
RUN npm install --production && npm run build

# Exponer puerto
EXPOSE 8000

# Comando por defecto
CMD php artisan serve --host=0.0.0.0 --port=8000
