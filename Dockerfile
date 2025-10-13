# Etapa 1: Node para compilar Vite
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json* pnpm-lock.yaml* yarn.lock* ./
# elige tu gestor: npm ci / pnpm i / yarn install
RUN npm ci
COPY . .
RUN npm run build   # -> genera /public/build

# Etapa 2: PHP + Apache (o FPM)
FROM php:8.3-apache
WORKDIR /var/www/html
# ... tu setup PHP habitual, enable mods, etc.
COPY . .
# Copia SOLO los assets compilados desde la etapa 1
COPY --from=assets /app/public/build /var/www/html/public/build
# permisos
RUN chown -R www-data:www-data storage bootstrap/cache public/build
