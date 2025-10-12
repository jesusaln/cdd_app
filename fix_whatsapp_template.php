<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 Corrigiendo configuración de WhatsApp...\n\n";

$empresa = App\Models\Empresa::first();

if (!$empresa) {
    echo "❌ No se encontró ninguna empresa configurada.\n";
    exit(1);
}

echo "🏢 Empresa: {$empresa->nombre_razon_social}\n";

// Corregir error tipográfico en la plantilla
$plantillaActual = $empresa->whatsapp_template_payment_reminder;
$plantillaCorregida = str_replace('recordarorio', 'recordatorio', $plantillaActual);

if ($plantillaActual !== $plantillaCorregida) {
    echo "📝 Corrigiendo nombre de plantilla:\n";
    echo "   De: '{$plantillaActual}'\n";
    echo "   A:  '{$plantillaCorregida}'\n";

    $empresa->whatsapp_template_payment_reminder = $plantillaCorregida;
    $empresa->save();

    echo "✅ Plantilla corregida exitosamente\n";
} else {
    echo "✅ El nombre de la plantilla ya está correcto\n";
}

echo "\n📋 CONFIGURACIÓN ACTUALIZADA:\n";
echo "Plantilla de recordatorio: {$empresa->whatsapp_template_payment_reminder}\n";

echo "\n💡 PRÓXIMOS PASOS:\n";
echo "1. Verifica que la plantilla '{$empresa->whatsapp_template_payment_reminder}' exista en Meta Business\n";
echo "2. Asegúrate de que esté aprobada para envío\n";
echo "3. Prueba el envío con: php artisan whatsapp:diagnose-connection\n";

echo "\n✅ Corrección completada\n";