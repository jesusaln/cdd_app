#!/bin/bash

# =====================================================
# DESPLIEGUE DOCKER - 3 COMANDOS SIMPLES
# =====================================================

echo "🐳 DESPLIEGUE DOCKER ULTRA-SIMPLE"
echo "==============================="

# =====================================================
# COMANDO 1: TRANSFERIR ARCHIVOS
# =====================================================

green "📦 COMANDO 1: Transferir archivos al VPS"

echo "Ejecuta en Windows PowerShell:"
echo ""
echo "scp 'cdd_app_*.zip' root@191.101.233.82:~/"
echo "scp '.env' root@191.101.233.82:~/"
echo "scp 'optimize_database.sql' root@191.101.233.82:~/"
echo ""

# =====================================================
# COMANDO 2: CONFIGURAR DOCKER
# =====================================================

green "🐳 COMANDO 2: Configurar Docker en VPS"

echo "Ejecuta en el VPS Ubuntu:"
echo ""
echo "# 1. Instalar Docker (si no está instalado)"
echo "curl -fsSL https://get.docker.com -o get-docker.sh"
echo "sudo sh get-docker.sh"
echo "sudo usermod -aG docker \$USER"
echo ""
echo "# 2. Instalar Docker Compose"
echo "sudo curl -L 'https://github.com/docker/compose/releases/latest/download/docker-compose-\$(uname -s)-\$(uname -m)' -o /usr/local/bin/docker-compose"
echo "sudo chmod +x /usr/local/bin/docker-compose"
echo ""
echo "# 3. Extraer aplicación"
echo "cd /var/www && unzip ~/cdd_app_*.zip && chown -R www-data:www-data cdd_app/"
echo ""
echo "# 4. Configurar permisos"
echo "chmod +x deploy_docker.sh"
echo ""

# =====================================================
# COMANDO 3: DESPLEGAR
# =====================================================

green "🚀 COMANDO 3: Desplegar aplicación"

echo "Ejecuta en el VPS:"
echo ""
echo "# Desplegar todo automáticamente"
echo "./deploy_docker.sh"
echo ""
echo "# O ejecutar paso a paso:"
echo "docker-compose build"
echo "docker-compose up -d"
echo "docker-compose exec app php artisan migrate --force"
echo ""

# =====================================================
# ACCESO FINAL
# =====================================================

green "🌐 ACCESO FINAL"

echo ""
blue "Después del despliegue:"
echo "📍 Aplicación: http://localhost (Nginx)"
echo "📍 Aplicación alternativa: http://localhost:3000"
echo "📍 Base de datos: localhost:5432"
echo "📍 Redis: localhost:6379"
echo ""

# =====================================================
# COMANDOS DE MANTENIMIENTO
# =====================================================

green "🔧 COMANDOS DE MANTENIMIENTO"

echo ""
echo "Ver estado de contenedores:"
echo "  docker-compose ps"
echo ""
echo "Ver logs:"
echo "  docker-compose logs -f"
echo ""
echo "Entrar al contenedor:"
echo "  docker-compose exec app bash"
echo ""
echo "Detener aplicación:"
echo "  docker-compose down"
echo ""
echo "Actualizar aplicación:"
echo "  docker-compose exec app php artisan migrate"
echo ""

green "✅ ¡DESPLIEGUE DOCKER LISTO!"

echo ""
blue "Este enfoque con Docker es:"
blue "✅ Más limpio que instalación directa"
blue "✅ Más fácil de mantener"
blue "✅ Más seguro"
blue "✅ Más escalable"
blue "✅ Perfecto para producción"

echo ""
green "🎯 VENTAJAS DE DOCKER:"
echo "🔹 Todos los servicios en contenedores aislados"
echo "🔹 Fácil backup y restauración"
echo "🔹 Configuración consistente"
echo "🔹 Fácil escalabilidad"
echo "🔹 Sin conflictos de puertos"

echo ""
blue "🚀 ¡Tu aplicación estará lista para miles de registros!"