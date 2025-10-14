<?php
try {
    $pdo = new PDO(
        'pgsql:host=127.0.0.1;port=5432;dbname=cdd_local',
        'cdd_user',
        'Contpaqi1.'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexión exitosa a PostgreSQL\n";

    // Consultar usuarios
    $stmt = $pdo->query("SELECT id, name, email FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Usuarios encontrados: " . count($users) . "\n";
    echo "----------------------------------------\n";

    foreach ($users as $user) {
        echo $user['id'] . ': ' . $user['name'] . ' (' . $user['email'] . ')' . "\n";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>