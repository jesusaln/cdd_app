<?php
return [
    'paths' => ['api/*'], // Rutas protegidas por CORS
    'allowed_methods' => ['*'], // Métodos HTTP permitidos
    'allowed_origins' => ['https://climasdeldesierto.laravel.cloud'], // Orígenes permitidos (tu frontend Ionic)
    'allowed_headers' => ['*'], // Encabezados permitidos
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false, // Habilita credenciales si es necesario
];
