<?php
return [
    'host' => env('RABBITMQ_HOST', '127.0.0.1'),
    'port' => env('RABBITMQ_PORT', 5672),
    'vhost' => env('RABBITMQ_VHOST', '/'),
    'login' => env('RABBITMQ_LOGIN', 'guest'),
    'password' => env('RABBITMQ_PASSWORD', 'guest'),
    'queue' => env('RABBITMQ_QUEUE', 'default'),
    'exchange' => env('RABBITMQ_EXCHANGE_NAME', 'default'),
    'key' => env('RABBITMQ_KEY', 'default'),
];