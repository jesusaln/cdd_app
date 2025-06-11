<?php
return [
    'paths' => ['api/*'], // Asegúrate de que esta ruta coincida con la estructura de tu API
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'], // Especifica los métodos permitidos
    'allowed_origins' => [
        'http://localhost:8100', // Desarrollo local
        'https://climasdeldesierto.laravel.cloud', // Producción
        'capacitor://localhost', // Apps móviles con Capacitor
    ],
    'allowed_origins_patterns' => [
        '^file:\/\/', // Más seguro y compatible con regex para file://
    ],
    'allowed_headers' => ['*'], // Permite todos los encabezados
    'exposed_headers' => [], // Deja vacío si no necesitas exponer encabezados específicos
    'max_age' => 86400, // Puedes ajustar este valor según tus necesidades de caché
    'supports_credentials' => false, // Cambia a true si necesitas manejar credenciales
];
