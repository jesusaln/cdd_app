<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🧪 Probando envío de mensaje de WhatsApp...\n\n";

$empresa = App\Models\Empresa::first();

if (!$empresa) {
    echo "❌ No se encontró ninguna empresa configurada.\n";
    exit(1);
}

echo "🏢 Empresa: {$empresa->nombre_razon_social}\n";
echo "📋 Plantilla configurada: {$empresa->whatsapp_template_payment_reminder}\n\n";

// Buscar un préstamo activo para prueba
$prestamo = App\Models\Prestamo::where('estado', 'activo')
    ->with('cliente')
    ->first();

if (!$prestamo) {
    echo "⚠️  No se encontró ningún préstamo activo para prueba\n";
    echo "💡 Crea un préstamo activo primero o usa un número de teléfono manual\n\n";

    echo "🔧 PRUEBA MANUAL:\n";
    echo "Puedes probar manualmente en: http://127.0.0.1:8000/prestamos\n";
    echo "1. Crea o selecciona un préstamo activo\n";
    echo "2. Haz clic en 'Enviar Recordatorio WhatsApp'\n";

    exit(0);
}

echo "📋 PRÉSTAMO DE PRUEBA:\n";
echo "   ID: {$prestamo->id}\n";
echo "   Cliente: {$prestamo->cliente->nombre_razon_social}\n";
echo "   Teléfono: {$prestamo->cliente->telefono}\n";
echo "   Estado: {$prestamo->estado}\n";
echo "   Monto pendiente: $" . number_format($prestamo->monto_pendiente, 2) . "\n\n";

echo "🔄 SIMULANDO ENVÍO DE RECORDATORIO...\n";

// Usar número de teléfono real para prueba
$telefonoOriginal = '6622036840'; // Tu número real
$telefonoFormateado = App\Services\WhatsAppService::formatPhoneToE164($telefonoOriginal);

echo "📱 Procesamiento de número:\n";
echo "   Original: {$telefonoOriginal}\n";
echo "   Formateado: {$telefonoFormateado}\n";

$templateParams = [
    'Usuario de Prueba', // Nombre para la plantilla
];

echo "📝 Parámetros de plantilla:\n";
echo "   Nombre: {$templateParams[0]}\n";
echo "   Nota: Usando plantilla 'saludo' con 1 parámetro\n\n";

// Crear servicio WhatsApp
try {
    $whatsappService = App\Services\WhatsAppService::fromEmpresa($empresa);

    echo "✅ Servicio WhatsApp creado exitosamente\n";
    echo "🔍 Validando plantilla '{$empresa->whatsapp_template_payment_reminder}'...\n";

    // Intentar enviar mensaje de prueba
    $response = $whatsappService->sendTemplate(
        $telefonoOriginal, // El servicio formateará automáticamente
        $empresa->whatsapp_template_payment_reminder,
        $empresa->whatsapp_default_language ?? 'es_MX',
        $templateParams
    );

    echo "🎉 ¡MENSAJE ENVIADO EXITOSAMENTE!\n";
    echo "📱 Message ID: " . ($response['messages'][0]['id'] ?? 'No disponible') . "\n";
    echo "✅ El sistema de WhatsApp está funcionando perfectamente\n";

} catch (\Exception $e) {
    echo "❌ Error al enviar mensaje: " . $e->getMessage() . "\n";

    if (strpos($e->getMessage(), 'número de teléfono inválido') !== false) {
        echo "\n💡 El cliente no tiene número de teléfono registrado\n";
        echo "📝 Agrega un número de teléfono al cliente primero\n";
    } elseif (strpos($e->getMessage(), 'plantilla') !== false) {
        echo "\n💡 La plantilla necesita ajustes en Meta Business\n";
        echo "📋 Consulta: WHATSAPP_TEMPLATE_GUIDE.md\n";
    }
}

echo "\n🔧 COMANDOS ÚTILES PARA DEPURACIÓN:\n";
echo "php artisan whatsapp:validate-token      # Validar token\n";
echo "php artisan whatsapp:list-templates     # Ver plantillas\n";
echo "php artisan whatsapp:diagnose-connection # Diagnóstico completo\n";

echo "\n✅ Prueba completada\n";