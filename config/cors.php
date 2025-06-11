<?php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_origins' => [
        '*', // Permite todos los orígenes

    ],
    'allowed_origins_patterns' => [
        // ¡CAMBIO AQUÍ!
        '/^file:\/\//', // Expresión regular para file://
        // Si también quieres cubrir subdominios HTTPS de tu dominio de producción
        '/^https:\/\/.*\.climasdeldesierto\.laravel\.cloud$/',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 86400,
    'supports_credentials' => false,
];
