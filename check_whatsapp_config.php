<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Verificando configuración de WhatsApp...\n\n";

$empresa = App\Models\Empresa::first();

if (!$empresa) {
    echo "❌ No se encontró ninguna empresa configurada.\n";
    echo "💡 Usa: php artisan whatsapp:configurar para crear una empresa con WhatsApp\n";
    exit(1);
}

echo "🏢 Empresa: {$empresa->nombre_razon_social}\n";
echo "WhatsApp habilitado: " . ($empresa->whatsapp_enabled ? '✅ SÍ' : '❌ NO') . "\n";

if (!$empresa->whatsapp_enabled) {
    echo "💡 Configura WhatsApp en: http://127.0.0.1:8000/empresa/configuracion\n";
    exit(0);
}

echo "\n📋 CONFIGURACIÓN ACTUAL:\n";
echo "Business Account ID: " . ($empresa->whatsapp_business_account_id ?: '❌ NO CONFIGURADO') . "\n";
echo "Phone Number ID: " . ($empresa->whatsapp_phone_number_id ?: '❌ NO CONFIGURADO') . "\n";
echo "Número de teléfono: " . ($empresa->whatsapp_sender_phone ?: '❌ NO CONFIGURADO') . "\n";
echo "Access Token: " . (!empty($empresa->whatsapp_access_token) ? '✅ CONFIGURADO' : '❌ NO CONFIGURADO') . "\n";
echo "App Secret: " . (!empty($empresa->whatsapp_app_secret) ? '✅ CONFIGURADO' : '❌ NO CONFIGURADO') . "\n";
echo "Plantilla de recordatorio: " . ($empresa->whatsapp_template_payment_reminder ?: '❌ NO CONFIGURADO') . "\n";

$missingFields = [];
$requiredFields = [
    'whatsapp_business_account_id' => 'Business Account ID',
    'whatsapp_phone_number_id' => 'Phone Number ID',
    'whatsapp_access_token' => 'Access Token',
    'whatsapp_template_payment_reminder' => 'Plantilla de recordatorio',
];

foreach ($requiredFields as $field => $label) {
    if (empty($empresa->$field)) {
        $missingFields[] = $label;
    }
}

if (!empty($missingFields)) {
    echo "\n⚠️  CAMPOS REQUERIDOS FALTANTES:\n";
    foreach ($missingFields as $field) {
        echo "  ❌ {$field}\n";
    }
    echo "\n💡 Configura estos campos en: http://127.0.0.1:8000/empresa/configuracion\n";
} else {
    echo "\n✅ Todos los campos requeridos están configurados\n";
    echo "🔧 Puedes probar la configuración con: php artisan whatsapp:test-phone-formatting\n";
}