# Usar imagen oficial de PHP 8.2 con FPM
FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
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
    sqlite3 \
    libsqlite3-dev \
    postgresql-client \
    && docker-php-ext-install pdo_mysql pdo_sqlite pdo_pgsql mbstring exif pcntl bcmath gd zip

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Crear usuario no-root para seguridad
RUN groupadd -g 1000 appuser && useradd -r -u 1000 -g appuser appuser

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar archivos de configuración de PHP
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# Copiar archivos del proyecto
COPY --chown=appuser:appuser . .

# Instalar dependencias de PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Instalar dependencias de Node.js y construir assets
RUN npm ci && npm run build

# Crear directorio de storage y asignar permisos
RUN mkdir -p storage/app storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R appuser:appuser storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Crear enlace simbólico para storage
RUN php artisan storage:link || true

# Cambiar al usuario no-root
USER appuser

# Exponer puerto
EXPOSE 8000

# Comando por defecto
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]