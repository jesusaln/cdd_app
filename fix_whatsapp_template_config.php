<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 Actualizando configuración de plantilla de WhatsApp...\n\n";

$empresa = App\Models\Empresa::first();

if (!$empresa) {
    echo "❌ No se encontró ninguna empresa configurada.\n";
    exit(1);
}

echo "🏢 Empresa: {$empresa->nombre_razon_social}\n";
echo "📋 Plantilla actual: {$empresa->whatsapp_template_payment_reminder}\n\n";

// Plantillas disponibles que están aprobadas
$plantillasDisponibles = [
    'saludo',
    'mensaje_de_bienvenida_climas_del_desierto',
    'hello_world'
];

echo "📱 PLANTILLAS APROBADAS DISPONIBLES:\n";
foreach ($plantillasDisponibles as $index => $plantilla) {
    echo "   " . ($index + 1) . ". {$plantilla}\n";
}

echo "\n💡 RECOMENDACIONES:\n";
echo "   • 'saludo' - Para mensajes de saludo generales\n";
echo "   • 'mensaje_de_bienvenida_climas_del_desierto' - Para bienvenida de clientes\n";
echo "   • 'hello_world' - Plantilla básica de prueba\n";

echo "\n🔧 Cambiando a plantilla 'saludo' (más apropiada para recordatorios)...\n";

$empresa->whatsapp_template_payment_reminder = 'saludo';
$empresa->save();

echo "✅ Plantilla actualizada exitosamente\n";
echo "📋 Nueva plantilla: {$empresa->whatsapp_template_payment_reminder}\n";

echo "\n🎉 ¡CONFIGURACIÓN ACTUALIZADA!\n";
echo "💡 Puedes probar el envío de recordatorios ahora\n";
echo "🔧 Prueba con: php artisan whatsapp:diagnose-connection\n";

echo "\n📝 NOTAS IMPORTANTES:\n";
echo "• La plantilla 'saludo' está APROBADA y lista para usar\n";
echo "• Puedes personalizar el contenido de la plantilla en Meta Business\n";
echo "• Para mensajes más específicos, crea una plantilla 'recordatorio_de_pago'\n";

echo "\n✅ Actualización completada\n";