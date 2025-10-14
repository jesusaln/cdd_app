<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🧪 Probando payload de WhatsApp...\n\n";

$empresa = App\Models\Empresa::first();

if (!$empresa) {
    echo "❌ No se encontró ninguna empresa configurada.\n";
    exit(1);
}

echo "🏢 Empresa: {$empresa->nombre_razon_social}\n";
echo "📱 Phone Number ID: {$empresa->whatsapp_phone_number_id}\n";
echo "📋 Plantilla: {$empresa->whatsapp_template_payment_reminder}\n\n";

// Payload de prueba mínimo
$testPayload = [
    'messaging_product' => 'whatsapp',
    'to' => '+525511111111', // Número de prueba
    'type' => 'template',
    'template' => [
        'name' => $empresa->whatsapp_template_payment_reminder,
        'language' => [
            'code' => 'es_MX'
        ]
    ]
];

echo "📤 PAYLOAD DE PRUEBA:\n";
echo json_encode($testPayload, JSON_PRETTY_PRINT) . "\n\n";

try {
    // Obtener token desencriptado
    $accessToken = decrypt($empresa->whatsapp_access_token);

    echo "🔗 Probando conexión directa...\n";

    $client = new GuzzleHttp\Client([
        'base_uri' => 'https://graph.facebook.com/v20.0/',
        'timeout' => 30,
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ],
    ]);

    // Primero probar obtener información del número
    echo "📞 Verificando Phone Number ID...\n";
    $response = $client->get($empresa->whatsapp_phone_number_id);
    $phoneInfo = json_decode($response->getBody()->getContents(), true);

    echo "✅ Phone Number ID válido\n";
    echo "   Estado: " . ($phoneInfo['account_mode'] ?? 'Desconocido') . "\n";
    echo "   Número: " . ($phoneInfo['display_phone_number'] ?? 'Desconocido') . "\n\n";

    // Si llega aquí, el problema podría estar en la plantilla o en el payload específico
    echo "💡 POSIBLES CAUSAS DEL ERROR:\n";
    echo "1. La plantilla '{$empresa->whatsapp_template_payment_reminder}' no existe\n";
    echo "2. La plantilla no está aprobada para envío\n";
    echo "3. El número de destino no es válido para pruebas\n";
    echo "4. La cuenta no tiene permisos para enviar mensajes\n\n";

    echo "🔧 SOLUCIONES RECOMENDADAS:\n";
    echo "1. Verifica que la plantilla exista en Meta Business Manager\n";
    echo "2. Asegúrate de que esté aprobada (estado 'Aprobada')\n";
    echo "3. Revisa que tengas permisos de 'business_management'\n";
    echo "4. Prueba con números que hayan interactuado con tu negocio\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";

    if (strpos($e->getMessage(), 'The payload is invalid') !== false) {
        echo "\n💡 El payload tiene formato inválido según la validación de Facebook\n";
        echo "Esto podría deberse a:\n";
        echo "- Campos requeridos faltantes en la plantilla\n";
        echo "- Parámetros mal formateados\n";
        echo "- Estructura del JSON incorrecta\n";
    }
}

echo "\n✅ Prueba completada\n";