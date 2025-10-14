<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🚀 MIGRACIÓN DE SQLITE A POSTGRESQL\n";
echo "==================================\n\n";

echo "📋 VERIFICANDO REQUISITOS...\n";

// Verificar si PostgreSQL está disponible
try {
    $pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=cdd_local', 'cdd_user', 'Contpaqi1.');
    echo "✅ PostgreSQL conectado exitosamente\n";
} catch (Exception $e) {
    echo "❌ Error conectando a PostgreSQL: " . $e->getMessage() . "\n";
    echo "💡 Verifica que PostgreSQL esté corriendo y las credenciales sean correctas:\n";
    echo "   Usuario: cdd_user\n";
    echo "   Password: Contpaqi1.\n";
    echo "   Base de datos: cdd_local\n\n";
    exit(1);
}

// Verificar si existe archivo SQLite
$sqlitePath = database_path('database.sqlite');
if (file_exists($sqlitePath)) {
    echo "✅ Archivo SQLite encontrado: " . $sqlitePath . "\n";
    $sqliteSize = filesize($sqlitePath);
    echo "📊 Tamaño: " . number_format($sqliteSize / 1024, 2) . " KB\n";
} else {
    echo "⚠️  Archivo SQLite no encontrado en: " . $sqlitePath . "\n";
    echo "💡 Continuando con migración de esquema...\n";
}

echo "\n🔧 CONFIGURANDO .ENV PARA POSTGRESQL...\n";

// Hacer backup del .env actual
if (file_exists('.env')) {
    copy('.env', '.env.sqlite.backup');
    echo "💾 Backup creado: .env.sqlite.backup\n";
}

// Actualizar .env para PostgreSQL
$envContent = file_get_contents('.env');
$envContent = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=pgsql', $envContent);
$envContent = preg_replace('/DB_HOST=.*/', 'DB_HOST=127.0.0.1', $envContent);
$envContent = preg_replace('/DB_PORT=.*/', 'DB_PORT=5432', $envContent);
$envContent = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=cdd_local', $envContent);
$envContent = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=cdd_user', $envContent);
$envContent = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD=Contpaqi1.', $envContent);

// Remover líneas de SQLite si existen
$envContent = preg_replace('/DB_DATABASE=.*\.sqlite.*/', '# DB_DATABASE= (SQLite removido)', $envContent);

file_put_contents('.env', $envContent);
echo "✅ .env actualizado para PostgreSQL\n";

echo "\n🗃️  EJECUTANDO MIGRACIONES...\n";

// Limpiar caché de configuración
echo "🧹 Limpiando caché...\n";
Artisan::call('config:clear');
Artisan::call('cache:clear');

echo "🔄 Ejecutando migraciones...\n";
try {
    Artisan::call('migrate', [], $output);
    echo "✅ Migraciones ejecutadas exitosamente\n";
} catch (Exception $e) {
    echo "❌ Error en migraciones: " . $e->getMessage() . "\n";
    echo "💡 Verifica que PostgreSQL esté corriendo correctamente\n";
    exit(1);
}

echo "\n📊 VERIFICANDO TABLAS CREADAS...\n";

// Verificar tablas en PostgreSQL
try {
    $tables = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'")->fetchAll(PDO::FETCH_COLUMN);

    echo "📋 Tablas creadas en PostgreSQL:\n";
    foreach ($tables as $table) {
        echo "   ✅ {$table}\n";
    }

    echo "\n🎉 ¡MIGRACIÓN COMPLETADA EXITOSAMENTE!\n";

} catch (Exception $e) {
    echo "❌ Error verificando tablas: " . $e->getMessage() . "\n";
}

echo "\n🔧 PRÓXIMOS PASOS RECOMENDADOS:\n";
echo "=============================\n";
echo "1. 🌐 Accede a pgAdmin: http://localhost:8081\n";
echo "   Usuario: admin@local.test\n";
echo "   Password: admin\n\n";

echo "2. 📊 Verifica datos (si migrando desde SQLite):\n";
echo "   php artisan db:seed  # Si tienes seeders\n\n";

echo "3. 🚀 Reinicia el servidor Laravel:\n";
echo "   php artisan serve --host=127.0.0.1 --port=8000\n\n";

echo "4. ✅ Prueba la aplicación:\n";
echo "   http://127.0.0.1:8000\n\n";

echo "📚 DOCUMENTACIÓN ADICIONAL:\n";
echo "• Docker: docker compose logs pg\n";
echo "• pgAdmin: http://localhost:8081\n";
echo "• Base de datos: localhost:5432/cdd_local\n\n";

echo "🔒 NOTAS DE SEGURIDAD:\n";
echo "• Credenciales actuales son para desarrollo\n";
echo "• Cambia passwords en producción\n";
echo "• Usa variables de entorno para configuración\n\n";

echo "✅ MIGRACIÓN COMPLETADA\n";