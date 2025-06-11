<?php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:8100',                // Para desarrollo local
        'https://climasdeldesierto.laravel.cloud', // Producción
        'capacitor://localhost',                // Para apps móviles con Capacitor
        'file://',                              // Para WebView en algunos dispositivos
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
