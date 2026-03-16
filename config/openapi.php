<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAPI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for generating OpenAPI/Swagger documentation
    |
    */

    'info' => [
        'title' => 'Laravel JWT API Documentation',
        'description' => 'API documentation for Laravel JWT Authentication System',
        'version' => '1.0.0',
        'contact' => [
            'name' => 'API Support',
            'email' => 'support@example.com',
        ],
    ],

    'servers' => [
        [
            'url' => env('APP_URL', 'http://localhost:8000'),
            'description' => 'API Server',
        ],
    ],

    'security' => [
        'api_key' => [
            'type' => 'http',
            'description' => 'JWT Bearer token authentication',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT',
        ],
    ],

    'scan' => [
        'paths' => [
            base_path('app/Http/Controllers'),
            base_path('app/Models'),
        ],
        'exclude' => [
            base_path('app/Http/Controllers/Controller.php'),
        ],
    ],

    'output' => [
        'json' => storage_path('app/public/openapi.json'),
        'yaml' => storage_path('app/public/openapi.yaml'),
    ],
];
