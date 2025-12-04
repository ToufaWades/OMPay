<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */
  'paths' => ['api/*', 'auth/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://ton-frontend.onrender.com', // ton domaine prod
        'http://localhost:34449', // Flutter Web dev
    ],

    'allowed_origins_patterns' => [
        '/http:\/\/localhost/', // Flutter Web dev, tous les ports locaux
        '/http:\/\/127\.0\.0\.1/', // Localhost IP
        '/http:\/\/10\.0\.2\.2/', // Android emulator
        '/http:\/\/192\.168\.\d+\.\d+/', // Local network
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // pour envoyer token

];
