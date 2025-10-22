<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PRUEBA DE VEHÍCULOS ACTIVOS ===\n\n";

try {
    // Probar consulta directa
    $count = App\Models\Carro::where('activo', true)->count();
    echo "Total vehículos activos: $count\n";

    // Probar consulta del controlador
    $carros = App\Models\Carro::select('id', 'marca', 'modelo', 'placa', 'kilometraje')
        ->where('activo', true)
        ->orderBy('marca')
        ->orderBy('modelo')
        ->get();

    echo "\nConsulta del controlador ejecutada exitosamente\n";
    echo "Vehículos encontrados: " . $carros->count() . "\n\n";

    foreach ($carros as $carro) {
        echo "- ID: {$carro->id}, {$carro->marca} {$carro->modelo} ({$carro->placa})\n";
    }

    echo "\n✅ Consulta funcionando correctamente\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}