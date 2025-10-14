<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cliente = App\Models\Cliente::find(11);
if ($cliente) {
    echo 'Cliente: ' . $cliente->nombre_razon_social . PHP_EOL;
    echo 'Email: ' . $cliente->email . PHP_EOL;
    echo 'Estado: ' . $cliente->estado . PHP_EOL;
    echo 'Régimen Fiscal: ' . $cliente->regimen_fiscal . PHP_EOL;
    echo 'Estado activo: ' . ($cliente->activo ? 'Sí' : 'No') . PHP_EOL;
} else {
    echo 'Cliente no encontrado' . PHP_EOL;
}