# Script simple para preparar GitHub en Windows
Write-Host "Preparando proyecto para GitHub..." -ForegroundColor Green

# Crear .gitignore
$gitignore = @"
/node_modules
/vendor
/storage/app/*
/storage/framework/*
/storage/logs/*
/bootstrap/cache/*
/.env
*.log
.DS_Store
Thumbs.db
"@

Set-Content -Path ".gitignore" -Value $gitignore
Write-Host "Archivo .gitignore creado" -ForegroundColor Green

# Crear README
$readme = @"
# CDD App - Sistema de Gestion

Aplicacion Laravel para gestion empresarial.

## Despliegue con Docker

git clone https://github.com/TU-USUARIO/cdd-app.git
cd cdd-app
./deploy_github.sh
"@

Set-Content -Path "README.md" -Value $readme
Write-Host "Archivo README.md creado" -ForegroundColor Green

# Crear paquete
try {
    tar -czf cdd_app_github.tar.gz --exclude=node_modules --exclude=vendor --exclude=storage/* --exclude=*.log cdd_app/
    Write-Host "Paquete cdd_app_github.tar.gz creado" -ForegroundColor Green
}
catch {
    Compress-Archive -Path "cdd_app\*" -DestinationPath "cdd_app_github.zip" -Force
    Write-Host "Paquete cdd_app_github.zip creado" -ForegroundColor Green
}

Write-Host ""
Write-Host "=== PASOS PARA GITHUB ===" -ForegroundColor Yellow
Write-Host "1. Crear repositorio en https://github.com/new" -ForegroundColor White
Write-Host "2. Subir el archivo cdd_app_github.tar.gz" -ForegroundColor White
Write-Host "3. En VPS: git clone https://github.com/TU-USUARIO/cdd-app.git" -ForegroundColor White
Write-Host "4. En VPS: cd cdd-app && ./deploy_github.sh" -ForegroundColor White

Write-Host ""
Write-Host "¡LISTO PARA DESPLEGAR!" -ForegroundColor Green