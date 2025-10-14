<?php
try {
    $pdo = new PDO(
        'pgsql:host=127.0.0.1;port=5432;dbname=cdd_local',
        'cdd_user',
        'Contpaqi1.'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexión exitosa a PostgreSQL\n";

    // Probar consulta simple
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Versión de PostgreSQL: " . $version['version'] . "\n";

    // Verificar si existe la tabla sessions
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'sessions'");
    $sessionsTable = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($sessionsTable) {
        echo "Tabla 'sessions' existe\n";
    } else {
        echo "Tabla 'sessions' NO existe\n";
    }

    // Verificar si existe la tabla cache
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'cache'");
    $cacheTable = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($cacheTable) {
        echo "Tabla 'cache' existe\n";
    } else {
        echo "Tabla 'cache' NO existe\n";
    }

} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
}
?>