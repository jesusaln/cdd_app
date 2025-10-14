<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_origins' => [
        'https://climasdeldesierto.laravel.cloud',
        // Orígenes específicos para Ionic
        'ionic://localhost',
        'http://localhost',
        'https://localhost',
        'capacitor://localhost',
    ],
    'allowed_origins_patterns' => [
        '/^https:\/\/.*\.climasdeldesierto\.laravel\.cloud$/',
        // Patrones específicos para Ionic
        '/^ionic:\/\//',
        '/^capacitor:\/\//',
        '/^http:\/\/localhost(:\d+)?$/',
        '/^https:\/\/localhost(:\d+)?$/',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 86400,
    'supports_credentials' => true, // Cambiado a true para apps móviles
];
