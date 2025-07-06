<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Documentation Title
    |--------------------------------------------------------------------------
    |
    | The title of your API documentation. This will be displayed in the
    | generated documentation and used as the OpenAPI specification title.
    |
    */
    'title' => env('API_DOCS_TITLE', 'API Documentation'),

    /*
    |--------------------------------------------------------------------------
    | API Documentation Version
    |--------------------------------------------------------------------------
    |
    | The version of your API. This will be used in the OpenAPI specification
    | and displayed in the documentation interface.
    |
    */
    'version' => env('API_DOCS_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | API Documentation Description
    |--------------------------------------------------------------------------
    |
    | A brief description of your API. This will be displayed in the
    | generated documentation.
    |
    */
    'description' => env('API_DOCS_DESCRIPTION', 'Generated API Documentation'),

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the routes for accessing the API documentation interface.
    |
    */
    'route_prefix' => env('API_DOCS_ROUTE_PREFIX', 'api-docs'),
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Default Theme
    |--------------------------------------------------------------------------
    |
    | The default theme to use when displaying the API documentation.
    | Available themes: swagger, redoc, rapidoc, custom
    |
    */
    'default_theme' => env('API_DOCS_DEFAULT_THEME', 'swagger'),

    /*
    |--------------------------------------------------------------------------
    | Available Themes
    |--------------------------------------------------------------------------
    |
    | Configure the available themes for the API documentation interface.
    | Each theme can have its own configuration options.
    |
    */
    'available_themes' => [
        'swagger' => [
            'name' => 'Swagger UI',
            'description' => 'Interactive API documentation with Swagger UI',
            'cdn_css' => 'https://unpkg.com/swagger-ui-dist@5.10.3/swagger-ui.css',
            'cdn_js' => 'https://unpkg.com/swagger-ui-dist@5.10.3/swagger-ui-bundle.js',
            'config' => [
                'deepLinking' => true,
                'displayOperationId' => false,
                'defaultModelsExpandDepth' => 1,
                'defaultModelExpandDepth' => 1,
                'defaultModelRendering' => 'example',
                'displayRequestDuration' => false,
                'docExpansion' => 'list',
                'filter' => false,
                'showExtensions' => false,
                'showCommonExtensions' => false,
                'tryItOutEnabled' => true
            ]
        ],
        'redoc' => [
            'name' => 'ReDoc',
            'description' => 'Beautiful API documentation with ReDoc',
            'cdn_js' => 'https://unpkg.com/redoc@2.1.3/bundles/redoc.standalone.js',
            'config' => [
                'scrollYOffset' => 0,
                'hideDownloadButton' => false,
                'disableSearch' => false,
                'hideLoading' => false,
                'nativeScrollbars' => false,
                'theme' => [
                    'colors' => [
                        'primary' => [
                            'main' => '#32329f'
                        ]
                    ],
                    'typography' => [
                        'fontSize' => '14px',
                        'lineHeight' => '1.5em',
                        'code' => [
                            'fontSize' => '13px'
                        ],
                        'headings' => [
                            'fontFamily' => 'Montserrat, sans-serif',
                            'fontWeight' => '400'
                        ]
                    ]
                ]
            ]
        ],
        'rapidoc' => [
            'name' => 'RapiDoc',
            'description' => 'Modern API documentation with RapiDoc',
            'cdn_js' => 'https://unpkg.com/rapidoc@9.3.4/dist/rapidoc-min.js',
            'config' => [
                'theme' => 'light',
                'bg-color' => '#ffffff',
                'text-color' => '#333333',
                'header-color' => '#005b96',
                'primary-color' => '#FF791A',
                'render-style' => 'read',
                'schema-style' => 'table',
                'default-schema-tab' => 'schema',
                'response-area-height' => '300px',
                'show-info' => 'true',
                'show-components' => 'true',
                'allow-authentication' => 'true',
                'allow-server-selection' => 'true',
                'allow-api-list-style-selection' => 'true'
            ]
        ],
        'custom' => [
            'name' => 'Custom Theme',
            'description' => 'Customizable theme for API documentation',
            'config' => [
                'primary_color' => '#3b82f6',
                'secondary_color' => '#64748b',
                'background_color' => '#ffffff',
                'text_color' => '#1f2937',
                'font_family' => 'Inter, system-ui, sans-serif'
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAPI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the OpenAPI specification generation.
    |
    */
    'openapi' => [
        'version' => '3.0.3',
        'servers' => [
            [
                'url' => env('APP_URL', 'http://localhost'),
                'description' => 'Development server'
            ]
        ],
        'contact' => [
            'name' => env('API_DOCS_CONTACT_NAME', 'API Support'),
            'email' => env('API_DOCS_CONTACT_EMAIL', 'support@example.com'),
            'url' => env('API_DOCS_CONTACT_URL', 'https://example.com/support')
        ],
        'license' => [
            'name' => env('API_DOCS_LICENSE_NAME', 'MIT'),
            'url' => env('API_DOCS_LICENSE_URL', 'https://opensource.org/licenses/MIT')
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Scanning Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which routes should be included in the API documentation.
    |
    */
    'scan_routes' => [
        'prefix' => env('API_DOCS_SCAN_PREFIX', 'api'),
        'exclude' => [
            'telescope',
            'horizon',
            'debugbar',
            '_ignition',
            'sanctum/csrf-cookie'
        ],
        'include_middleware' => [
            'api',
            'auth:api',
            'auth:sanctum'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Static Generation Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for generating static HTML documentation files.
    |
    */
    'static' => [
        'output_path' => storage_path('api-docs/static'),
        'base_url' => env('API_DOCS_STATIC_BASE_URL', ''),
        'themes' => ['swagger', 'redoc'],
        'minify_html' => env('API_DOCS_STATIC_MINIFY', false),
        'include_assets' => true,
        'generate_sitemap' => true,
        'assets' => [
            'favicon' => null,
            'logo' => null,
            'custom_css' => null,
            'custom_js' => null
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for generated documentation to improve performance.
    |
    */
    'cache' => [
        'enabled' => env('API_DOCS_CACHE_ENABLED', true),
        'ttl' => env('API_DOCS_CACHE_TTL', 3600), // 1 hour
        'key_prefix' => 'api_docs',
        'store' => env('API_DOCS_CACHE_STORE', 'file')
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security settings for the API documentation.
    |
    */
    'security' => [
        'enabled' => env('API_DOCS_SECURITY_ENABLED', false),
        'middleware' => ['auth'],
        'allowed_ips' => [],
        'basic_auth' => [
            'enabled' => false,
            'username' => env('API_DOCS_USERNAME'),
            'password' => env('API_DOCS_PASSWORD')
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Configuration
    |--------------------------------------------------------------------------
    |
    | Configure export options for the API documentation.
    |
    */
    'export' => [
        'formats' => ['json', 'yaml'],
        'pretty_print' => true,
        'include_examples' => true,
        'include_schemas' => true
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Customization
    |--------------------------------------------------------------------------
    |
    | Customize the appearance and behavior of the documentation interface.
    |
    */
    'ui' => [
        'show_header' => true,
        'show_footer' => true,
        'show_theme_selector' => true,
        'show_export_buttons' => true,
        'custom_css' => null,
        'custom_js' => null,
        'meta_tags' => [
            'description' => 'API Documentation',
            'keywords' => 'api, documentation, openapi, swagger',
            'author' => 'X-Multibyte'
        ]
    ]
];
