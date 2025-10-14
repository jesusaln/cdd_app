#!/bin/bash

# =====================================================
# PREPARACIÓN COMPLETA PARA GITHUB - UN SOLO COMANDO
# =====================================================

echo "🚀 PREPARANDO PROYECTO PARA GITHUB - AUTOMÁTICO"
echo "=============================================="

# Función para colores
green() { echo -e "\033[32m$1\033[0m"; }
blue() { echo -e "\033[34m$1\033[0m"; }

# Ejecutar scripts de preparación
chmod +x setup_github_deploy.sh
./setup_github_deploy.sh

chmod +x export_github.sh
./export_github.sh

green "✅ PREPARACIÓN COMPLETA!"

echo ""
blue "=== PASOS PARA SUBIR A GITHUB ==="
echo ""
echo "1️⃣ Crear repositorio en GitHub:"
echo "   📍 Ir a: https://github.com/new"
echo "   📝 Nombre del repositorio: cdd-app"
echo "   📝 Descripción: Sistema de gestión Laravel con Docker"
echo "   ✅ Marcar 'Add a README file'"
echo ""
echo "2️⃣ Subir archivos:"
echo "   💻 Ejecutar estos comandos en tu máquina:"
echo ""
echo "   # Crear directorio temporal"
echo "   mkdir temp_github"
echo "   cd temp_github"
echo ""
echo "   # Extraer paquete"
echo "   tar -xzf ../cdd_app_github.tar.gz"
echo ""
echo "   # Inicializar git"
echo "   git init"
echo "   git add ."
echo "   git commit -m 'Initial commit - CDD App Laravel'"
echo ""
echo "   # Conectar con GitHub (cambiar TU-USUARIO)"
echo "   git remote add origin https://github.com/TU-USUARIO/cdd-app.git"
echo "   git branch -M main"
echo "   git push -u origin main"
echo ""
echo "3️⃣ Desplegar en VPS:"
echo "   🖥️ En el VPS Ubuntu ejecutar:"
echo ""
echo "   # Clonar desde GitHub"
echo "   git clone https://github.com/TU-USUARIO/cdd-app.git"
echo "   cd cdd-app"
echo ""
echo "   # Desplegar automáticamente"
echo "   ./deploy_github.sh"
echo ""

green "=== ARCHIVOS PREPARADOS ==="
echo "📦 cdd_app_github.tar.gz - Paquete para GitHub"
echo "📋 .gitignore - Archivos a ignorar"
echo "📖 README.md - Documentación"
echo "🚀 deploy_github.sh - Despliegue automático"
echo "🐳 Dockerfile - Imagen Docker"
echo "⚙️ docker-compose.yml - Servicios"

echo ""
green "🎯 VENTAJAS DE ESTE ENFOQUE:"
echo "✅ Despliegue ultra-rápido"
echo "✅ Sin transferencia manual"
echo "✅ Version control completo"
echo "✅ Fácil mantenimiento"
echo "✅ Trabajo colaborativo"

echo ""
blue "🚀 COMANDO FINAL EN VPS:"
blue "git clone https://github.com/TU-USUARIO/cdd-app.git && cd cdd-app && ./deploy_github.sh"

echo ""
green "¡TODO LISTO PARA DESPLEGAR! 🎉"