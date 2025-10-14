<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_origins' => [
        'https://climasdeldesierto.laravel.cloud',
        // Orígenes específicos para desarrollo local
        'ionic://localhost',
        'http://localhost',
        'https://localhost',
        'http://localhost:8000',
        'https://localhost:8000',
        'http://127.0.0.1:8000',
        'https://127.0.0.1:8000',
        'http://0.0.0.0:8000',
        'https://0.0.0.0:8000',
        'capacitor://localhost',
        // Permitir cualquier origen localhost con puerto
        'http://localhost:*',
        'https://localhost:*',
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
