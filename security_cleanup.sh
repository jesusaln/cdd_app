#!/bin/bash

# 🚨 Script de Emergencia para Limpiar Información Sensible de Git
# Ejecutar con precaución - modifica el historial de Git

echo "🚨 INICIANDO LIMPIEZA DE SEGURIDAD DE GIT"
echo "=========================================="

# Verificar si estamos en un repositorio Git
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo "❌ No se encuentra un repositorio Git válido"
    exit 1
fi

echo "✅ Repositorio Git detectado"

# Crear respaldo antes de limpiar
echo "💾 Creando respaldo del estado actual..."
git branch backup-before-security-cleanup-$(date +%Y%m%d-%H%M%S)

# Archivos que contienen información sensible
SENSITIVE_FILES=(
    "whatsapp.dev.json"
    "*.env"
    "*.key"
    "*.pem"
    "config/database.php"
)

echo "🔍 Buscando información sensible en el historial..."

# Buscar tokens de WhatsApp en el historial
echo "🔍 Buscando tokens de WhatsApp en el historial..."
git log -p --all | grep -i "EAAt" | head -5

if [ $? -eq 0 ]; then
    echo "⚠️  Se encontraron posibles tokens en el historial"
else
    echo "✅ No se encontraron tokens en el historial reciente"
fi

echo ""
echo "🧹 LIMPIANDO ARCHIVOS SENSIBLES..."

# Eliminar archivos sensibles del historial
for file in "${SENSITIVE_FILES[@]}"; do
    if git ls-files | grep -q "^$file$"; then
        echo "🗑️  Eliminando $file del historial..."
        git filter-branch --force --index-filter "git rm --cached --ignore-unmatch $file" --prune-empty --tag-name-filter cat -- --all
    fi
done

echo ""
echo "🔄 LIMPIANDO REFS Y RECOLECTANDO BASURA..."

# Limpiar referencias y recolectar basura
git for-each-ref --format='delete %(refname)' refs/original | git update-ref --stdin
git reflog expire --expire=now --all
git gc --prune=now

echo ""
echo "✅ LIMPIEZA COMPLETADA"
echo ""
echo "📋 PRÓXIMOS PASOS IMPORTANTES:"
echo "1. 🚨 Regenera el token de WhatsApp comprometido"
echo "2. 📝 Actualiza el archivo whatsapp.dev.json con el nuevo token"
echo "3. 🔄 Haz commit de los cambios"
echo "4. 🚀 Force push si es necesario (git push --force-with-lease)"
echo ""
echo "⚠️  ADVERTENCIA:"
echo "Esta operación modifica el historial de Git."
echo "Asegúrate de que todos los colaboradores tengan los cambios actualizados."

echo ""
echo "🔧 Para regenerar el token:"
echo "1. Ve a Meta Business Manager"
echo "2. Crea un nuevo System User token"
echo "3. Ejecuta: php update_whatsapp_token.php"