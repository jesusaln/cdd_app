<?php
return [
    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [
        'http://localhost:8100',
        'capacitor://localhost',
        'file://',
    ],

    'allowed_origins_patterns' => [
        '^https?:\/\/.*\.climasdeldesierto\.laravel\.cloud$', // Acepta HTTP y HTTPS con subdominios
        '^https?:\/\/climasdeldesierto\.laravel\.cloud$',     // También acepta el dominio raíz
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,
];
