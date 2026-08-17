<?php

$origins = array_values(array_unique(array_filter(array_map(
    static fn (string $origin) => rtrim(trim($origin), '/'),
    array_merge(
        [(string) env('FRONTEND_URL', '')],
        explode(',', (string) env(
            'CORS_ALLOWED_ORIGINS',
            '*',
        )),
    ),
))));

$allowedOrigins = in_array('*', $origins, true) ? ['*'] : $origins;

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
    | Danh sách origin: CORS_ALLOWED_ORIGINS (phẩy) + FRONTEND_URL.
    | Origin phải đúng scheme + host, không có dấu / cuối.
    | Đặt CORS_ALLOWED_ORIGINS=* để cho phép mọi domain.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
