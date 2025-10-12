<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔐 Actualizando token de WhatsApp...\n\n";

// El nuevo token proporcionado por el usuario
$newToken = 'EAAtsyGzO6SkBPoXDZCmAeFeE5mQasMSm1ENZC4d92YWlKZCHosDCiZCB18z1ajmfLgdaNmA23ZAJfUWk8QX8AzxeSdzJjbkFqvv0fXlIgdSF2brM5pRp9ssZB595GO2VuxlHZAHM4FDi9yTp0TlA7UPHe8JgWZAZBol6IoyZAOzu5YTn4e3LtQHxvduJoCOCi11gZDZD';

echo "📋 Nuevo token recibido (longitud: " . strlen($newToken) . " caracteres)\n";
echo "🔐 Estado: ";

try {
    // Encriptar el token antes de guardarlo
    $encryptedToken = encrypt($newToken);
    echo "✅ Encriptado correctamente\n";

    // Actualizar en la base de datos
    $empresa = App\Models\Empresa::first();

    if (!$empresa) {
        echo "❌ No se encontró ninguna empresa configurada.\n";
        exit(1);
    }

    $empresa->whatsapp_access_token = $encryptedToken;
    $empresa->save();

    echo "💾 Token actualizado en la base de datos\n";
    echo "🏢 Empresa: {$empresa->nombre_razon_social}\n";

    // Validar inmediatamente el token
    echo "\n🔍 Validando token...\n";

    $client = new GuzzleHttp\Client([
        'base_uri' => 'https://graph.facebook.com/v20.0/',
        'timeout' => 30,
        'headers' => [
            'Authorization' => 'Bearer ' . $newToken,
            'Content-Type' => 'application/json',
        ],
    ]);

    $response = $client->get($empresa->whatsapp_phone_number_id . '?fields=id,display_phone_number,account_mode');
    $data = json_decode($response->getBody()->getContents(), true);

    echo "✅ TOKEN VÁLIDO Y FUNCIONAL\n";
    echo "📱 Phone Number ID: {$data['id']}\n";
    echo "📞 Número: " . ($data['display_phone_number'] ?? 'No disponible') . "\n";
    echo "🏢 Modo: " . ($data['account_mode'] ?? 'No disponible') . "\n";

    echo "\n🎉 ¡TODO LISTO!\n";
    echo "💡 Puedes probar el envío de mensajes ahora\n";
    echo "🔧 Comando de prueba: php artisan whatsapp:diagnose-connection\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";

    if (strpos($e->getMessage(), 'Error validating access token') !== false) {
        echo "\n💡 El token podría ser inválido o haber expirado ya\n";
        echo "🔄 Genera un nuevo token en Meta Business\n";
    }
}

echo "\n✅ Actualización completada\n";