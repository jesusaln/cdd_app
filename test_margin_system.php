<?php

require_once 'vendor/autoload.php';

use App\Services\MarginService;
use App\Models\Producto;

// Crear productos de prueba con márgenes insuficientes
echo "=== CREANDO PRODUCTOS DE PRUEBA ===\n";

$productosPrueba = [
    [
        'nombre' => 'Producto Margen Insuficiente 1',
        'precio_compra' => 100.00,
        'precio_venta' => 102.00, // Solo 2% margen
    ],
    [
        'nombre' => 'Producto Margen Insuficiente 2',
        'precio_compra' => 200.00,
        'precio_venta' => 204.00, // Solo 2% margen
    ],
    [
        'nombre' => 'Producto Margen Bueno',
        'precio_compra' => 150.00,
        'precio_venta' => 165.00, // 10% margen - válido
    ]
];

$productosIds = [];

foreach ($productosPrueba as $datosProducto) {
    $producto = Producto::create([
        'nombre' => $datosProducto['nombre'],
        'descripcion' => 'Producto de prueba para márgenes',
        'precio_compra' => $datosProducto['precio_compra'],
        'precio_venta' => $datosProducto['precio_venta'],
        'stock' => 10,
        'categoria_id' => 1,
        'codigo' => 'TEST-' . rand(1000, 9999)
    ]);

    $productosIds[] = $producto->id;

    echo "Creado: {$producto->nombre} (ID: {$producto->id})\n";
    echo "  Precio compra: \${$producto->precio_compra}\n";
    echo "  Precio venta: \${$producto->precio_venta}\n";

    $service = new MarginService();
    $validacion = $service->validarMargen($producto, $producto->precio_venta);
    echo "  Margen válido: " . ($validacion['valido'] ? 'SÍ' : 'NO') . "\n";
    echo "  Margen actual: {$validacion['margen_actual']}%\n";
    echo "  Margen requerido: {$validacion['margen_requerido']}%\n";
    echo "---\n";
}

echo "\n=== PRUEBA DEL SERVICIO DE MÁRGENES ===\n";

// Simular datos como los que envía el formulario
$productosFormulario = [];
foreach ($productosIds as $id) {
    $producto = Producto::find($id);
    $productosFormulario[] = [
        'id' => $producto->id,
        'tipo' => 'producto',
        'precio' => $producto->precio_venta,
        'cantidad' => 1,
        'descuento' => 0
    ];
}

$service = new MarginService();
$validacion = $service->validarMargenesProductos($productosFormulario);

echo "Resultado de validación:\n";
echo "  Todos válidos: " . ($validacion['todos_validos'] ? 'SÍ' : 'NO') . "\n";
echo "  Productos bajo margen: " . count($validacion['productos_bajo_margen']) . "\n";

if (!$validacion['todos_validos']) {
    echo "\nProductos con margen insuficiente:\n";
    foreach ($validacion['productos_bajo_margen'] as $item) {
        $producto = $item['producto'];
        echo "  - {$producto->nombre}: \${$item['precio_actual']} (debería ser \${$item['precio_sugerido']})\n";
    }

    echo "\nMensaje de advertencia:\n";
    echo $service->generarMensajeAdvertencia($validacion['productos_bajo_margen']);
}

echo "\n=== IDs de productos de prueba creados ===\n";
echo "Usa estos IDs para probar el modal en las cotizaciones:\n";
echo implode(', ', $productosIds) . "\n";

echo "\n=== INSTRUCCIONES PARA PROBAR ===\n";
echo "1. Ve a crear una nueva cotización\n";
echo "2. Selecciona un cliente\n";
echo "3. Agrega los productos con IDs: " . implode(', ', $productosIds) . "\n";
echo "4. Haz clic en 'Crear Cotización'\n";
echo "5. Deberías ver el modal de alerta de márgenes insuficientes\n";
