<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Documentation Configuration
    |--------------------------------------------------------------------------
    */

    'title' => env('API_DOCS_TITLE', 'API Documentation'),
    'description' => env('API_DOCS_DESCRIPTION', 'Generated API Documentation'),
    'version' => env('API_DOCS_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */

    'route_prefix' => 'api-docs',
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | UI Themes
    |--------------------------------------------------------------------------
    */

    'default_theme' => 'swagger',
    'available_themes' => [
        'swagger' => 'Swagger UI',
        'redoc' => 'ReDoc',
        'rapidoc' => 'RapiDoc',
        'custom' => 'Custom Theme',
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAPI Configuration
    |--------------------------------------------------------------------------
    */

    'openapi' => [
        'version' => '3.0.3',
        'servers' => [
            [
                'url' => env('APP_URL', 'http://localhost'),
                'description' => 'Development Server',
            ],
        ],
        'security' => [
            'bearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'JWT',
            ],
            'apiKey' => [
                'type' => 'apiKey',
                'in' => 'header',
                'name' => 'X-API-Key',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Scanning
    |--------------------------------------------------------------------------
    */

    'scan_routes' => [
        'prefix' => 'api',
        'exclude' => [
            'telescope',
            'horizon',
            'nova-api',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Settings
    |--------------------------------------------------------------------------
    */

    'export' => [
        'formats' => ['json', 'yaml'],
        'path' => storage_path('api-docs'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Static Generation
    |--------------------------------------------------------------------------
    */

    'static' => [
        'enabled' => true,
        'output_path' => public_path('api-docs-static'),
        'base_url' => env('API_DOCS_STATIC_BASE_URL', '/api-docs-static'),
        'themes' => ['swagger', 'redoc', 'rapidoc', 'custom'],
        'include_assets' => true,
        'minify_html' => false,
        'generate_sitemap' => true,
    ],
];
