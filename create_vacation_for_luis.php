<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\RegistroVacaciones;
use App\Models\Vacacion;
use Carbon\Carbon;

try {
    // Buscar usuario Luis
    $luis = User::where('name', 'like', '%Luis%')->first();
    if (!$luis) {
        echo "No se encontró usuario Luis\n";
        exit(1);
    }

    echo "Usuario encontrado: " . $luis->name . " (ID: " . $luis->id . ")\n";

    // Verificar si ya existe registro de vacaciones
    $registro = RegistroVacaciones::where('user_id', $luis->id)->where('anio', 2025)->first();
    if (!$registro) {
        // Crear registro de vacaciones manualmente
        $registro = RegistroVacaciones::create([
            'user_id' => $luis->id,
            'anio' => 2025,
            'dias_correspondientes' => 14,
            'dias_disponibles' => 14,
            'dias_utilizados' => 0,
            'dias_pendientes' => 14,
            'dias_acumulados_siguiente' => 0,
            'fecha_calculo' => now(),
        ]);
        echo "Registro de vacaciones creado: ID " . $registro->id . "\n";
    } else {
        echo "Registro de vacaciones ya existe: ID " . $registro->id . "\n";
    }

    // Crear vacación para hoy
    $hoy = Carbon::today()->toDateString();
    $vacacion = Vacacion::create([
        'user_id' => $luis->id,
        'fecha_inicio' => $hoy,
        'fecha_fin' => $hoy,
        'dias_solicitados' => 1,
        'dias_pendientes' => 1,
        'motivo' => 'Vacación de prueba creada automáticamente',
        'estado' => 'pendiente',
    ]);

    echo "✅ Vacación creada exitosamente:\n";
    echo "   - ID: " . $vacacion->id . "\n";
    echo "   - Fecha: " . $hoy . "\n";
    echo "   - Días: 1\n";
    echo "   - Estado: Pendiente\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}