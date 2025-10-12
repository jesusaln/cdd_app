# 🚨 Script de Emergencia para Limpiar Información Sensible de Git
# Ejecutar con precaución - modifica el historial de Git

Write-Host "🚨 INICIANDO LIMPIEZA DE SEGURIDAD DE GIT" -ForegroundColor Red
Write-Host "==========================================" -ForegroundColor Red

# Verificar si estamos en un repositorio Git
try {
    $gitDir = git rev-parse --git-dir 2>$null
    if (!$gitDir) {
        throw "No se encuentra un repositorio Git válido"
    }
} catch {
    Write-Host "❌ $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Repositorio Git detectado" -ForegroundColor Green

# Crear respaldo antes de limpiar
$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$backupBranch = "backup-before-security-cleanup-$timestamp"

Write-Host "💾 Creando respaldo del estado actual..." -ForegroundColor Yellow
git branch $backupBranch

# Archivos que contienen información sensible
$sensitiveFiles = @(
    "whatsapp.dev.json",
    "*.env",
    "*.key",
    "*.pem",
    "config/database.php"
)

Write-Host "🔍 Buscando información sensible en el historial..." -ForegroundColor Yellow

# Buscar tokens de WhatsApp en el historial
Write-Host "🔍 Buscando tokens de WhatsApp en el historial..." -ForegroundColor Yellow
try {
    $tokensFound = git log -p --all | Select-String -Pattern "EAAt" | Select-Object -First 5
    if ($tokensFound) {
        Write-Host "⚠️  Se encontraron posibles tokens en el historial:" -ForegroundColor Red
        $tokensFound | ForEach-Object { Write-Host "   $($_.Line)" -ForegroundColor Red }
    } else {
        Write-Host "✅ No se encontraron tokens en el historial reciente" -ForegroundColor Green
    }
} catch {
    Write-Host "ℹ️  No se pudo buscar en el historial completo" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "🧹 LIMPIANDO ARCHIVOS SENSIBLES..." -ForegroundColor Yellow

# Eliminar archivos sensibles del historial
foreach ($file in $sensitiveFiles) {
    try {
        $fileExists = git ls-files | Where-Object { $_ -eq $file }
        if ($fileExists) {
            Write-Host "🗑️  Eliminando $file del historial..." -ForegroundColor Yellow
            git filter-branch --force --index-filter "git rm --cached --ignore-unmatch '$file'" --prune-empty --tag-name-filter cat -- --all
        }
    } catch {
        Write-Host "ℹ️  No se pudo procesar $file" -ForegroundColor Gray
    }
}

Write-Host ""
Write-Host "🔄 LIMPIANDO REFS Y RECOLECTANDO BASURA..." -ForegroundColor Yellow

# Limpiar referencias y recolectar basura
try {
    git for-each-ref --format='delete %(refname)' refs/original | git update-ref --stdin 2>$null
    git reflog expire --expire=now --all 2>$null
    git gc --prune=now 2>$null
    Write-Host "✅ Limpieza de referencias completada" -ForegroundColor Green
} catch {
    Write-Host "⚠️  Algunos pasos de limpieza fallaron (normal en algunos casos)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "✅ LIMPIEZA COMPLETADA" -ForegroundColor Green
Write-Host ""
Write-Host "📋 PRÓXIMOS PASOS IMPORTANTES:" -ForegroundColor Cyan
Write-Host "1. 🚨 Regenera el token de WhatsApp comprometido" -ForegroundColor Red
Write-Host "2. 📝 Actualiza el archivo whatsapp.dev.json con el nuevo token" -ForegroundColor Yellow
Write-Host "3. 🔄 Haz commit de los cambios" -ForegroundColor Yellow
Write-Host "4. 🚀 Force push si es necesario (git push --force-with-lease)" -ForegroundColor Red
Write-Host ""
Write-Host "⚠️  ADVERTENCIA:" -ForegroundColor Red
Write-Host "Esta operación modifica el historial de Git." -ForegroundColor Red
Write-Host "Asegúrate de que todos los colaboradores tengan los cambios actualizados." -ForegroundColor Red

Write-Host ""
Write-Host "🔧 Para regenerar el token:" -ForegroundColor Cyan
Write-Host "1. Ve a Meta Business Manager" -ForegroundColor White
Write-Host "2. Crea un nuevo System User token" -ForegroundColor White
Write-Host "3. Ejecuta: php update_whatsapp_token.php" -ForegroundColor White

Write-Host ""
Write-Host "📞 Para ayuda inmediata: Consulta WHATSAPP_SECURE_CONFIG.md" -ForegroundColor Green