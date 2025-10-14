#!/bin/bash
# SUPER SIMPLE FIX - PDO POSTGRESQL
echo "🚀 EJECUTANDO SOLUCIÓN ULTRA SIMPLE..."
cd ~/cdd_app && \
echo "1. Instalando dependencias críticas..." && \
apt-get update && apt-get install -y postgresql-server-dev-all libpq-dev && \
echo "2. Limpiando Docker..." && \
docker-compose down 2>/dev/null; docker system prune -f; docker builder prune -f && \
echo "3. Construyendo imagen..." && \
docker build --no-cache -t cdd_app_simple . && \
echo "4. Verificando extensiones..." && \
docker run --rm cdd_app_simple php -m | grep pdo_pgsql && echo "✅ PDO PostgreSQL FUNCIONA" || echo "❌ Aún hay problemas" && \
echo "5. Iniciando aplicación..." && \
docker-compose up --build -d && \
echo "🎉 ¡LISTO!"