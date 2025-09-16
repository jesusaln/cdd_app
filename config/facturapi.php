<?php
return [
    'mode' => env('FACTURAPI_MODE', 'test'),
    'keys' => [
        'test' => env('FACTURAPI_KEY_TEST'),
        'live' => env('FACTURAPI_KEY_LIVE'),
    ],
    'defaults' => [
        'payment_form' => env('FACTURAPI_DEFAULT_PAYMENT_FORM', '03'),
        'use'          => env('FACTURAPI_DEFAULT_USE', 'G03'),
    ],
];
