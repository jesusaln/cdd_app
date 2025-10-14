
<?php
echo "📊 INFORMACIÓN DE LA BASE DE DATOS\n";
echo "================================\n\n";

try {
    // Conectar a PostgreSQL
    $pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=cdd_local', 'cdd_user', 'Contpaqi1.');
    echo "✅ Conexión exitosa a PostgreSQL\n\n";

    // Obtener información de la base de datos
    echo "🗃️  DETALLES DE LA BASE DE DATOS:\n";
    echo "==============================\n";
    echo "📋 Nombre: cdd_local\n";
    echo "🌐 Host: 127.0.0.1:5432\n";
