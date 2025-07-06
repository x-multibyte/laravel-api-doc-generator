# Laravel API Documentation Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/x-multibyte/laravel-api-docs.svg?style=flat-square)](https://packagist.org/packages/x-multibyte/laravel-api-docs)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/x-multibyte/laravel-api-docs/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/x-multibyte/laravel-api-docs/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/x-multibyte/laravel-api-docs/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/x-multibyte/laravel-api-docs/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/x-multibyte/laravel-api-docs.svg?style=flat-square)](https://packagist.org/packages/x-multibyte/laravel-api-docs)

A comprehensive Laravel package for automatically generating beautiful API documentation from your Laravel routes. Supports multiple UI themes including Swagger UI, ReDoc, and RapiDoc with OpenAPI 3+ specification.

## Features

- 🚀 **Automatic Documentation Generation** - Scans your Laravel routes and generates OpenAPI 3+ specifications
- 🎨 **Multiple UI Themes** - Choose from Swagger UI, ReDoc, RapiDoc, or create custom themes
- 📱 **Responsive Design** - Works perfectly on desktop, tablet, and mobile devices
- 🔧 **Highly Configurable** - Extensive configuration options for customization
- 📤 **Export Support** - Export documentation as JSON or YAML files
- 📥 **Import Support** - Import existing OpenAPI specifications
- 🗂️ **Static Generation** - Generate static HTML files for hosting anywhere
- 🧹 **Cleanup Tools** - Built-in commands for managing generated files
- 🔍 **Route Analysis** - Detailed analysis and statistics of your API routes
- 🛡️ **Security Support** - Handles authentication middleware detection
- ⚡ **Performance Optimized** - Caching support for improved performance
- 🧪 **Comprehensive Testing** - Full test suite with factory patterns

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+
- Symfony YAML component

## Installation

You can install the package via Composer:

```bash
composer require x-multibyte/laravel-api-docs
```

### Laravel Auto-Discovery

The package will automatically register its service provider through Laravel's package auto-discovery feature.

### Manual Registration (Optional)

If you need to manually register the service provider, add it to your `config/app.php`:

```php
'providers' => [
    // Other Service Providers
    XMultibyte\ApiDoc\ApiDocsServiceProvider::class,
],
```

## Quick Start

1. **Publish the configuration file:**

```bash
php artisan api-docs:publish --config
```

2. **Generate your API documentation:**

```bash
php artisan api-docs:generate
```

3. **View your documentation:**

Visit `http://your-app.test/api-docs` in your browser.

## Configuration

The configuration file will be published to `config/api-docs.php`. Here are the key configuration options:

```php
return [
    // Basic API information
    'title' => 'My API Documentation',
    'version' => '1.0.0',
    'description' => 'Comprehensive API documentation',

    // Route configuration
    'route_prefix' => 'api-docs',
    'middleware' => ['web'],

    // Default theme
    'default_theme' => 'swagger',

    // Route scanning
    'scan_routes' => [
        'prefix' => 'api',
        'exclude' => ['telescope', 'horizon']
    ],

    // OpenAPI configuration
    'openapi' => [
        'version' => '3.0.3',
        'servers' => [
            [
                'url' => env('APP_URL'),
                'description' => 'Development server'
            ]
        ]
    ]
];
```

## Available Commands

### Generate Documentation

Generate API documentation from your Laravel routes:

```bash
Basic generation
php artisan api-docs:generate
```

```bash
# Generate with specific format
php artisan api-docs:generate --format=yaml
```

```bash
# Generate both JSON and YAML
php artisan api-docs:generate --format=both
```

```bash
# Generate with validation
php artisan api-docs:generate --validate
```

```bash
# Generate specific routes only
php artisan api-docs:generate --routes="api/users/*,api/posts/*"
```

```bash
# Exclude specific routes
php artisan api-docs:generate --exclude="api/admin/*"
```

### Import Documentation

Import existing OpenAPI specifications:

```bash
# Import from JSON file
php artisan api-docs:import openapi.json
```

```bash
# Import with validation and backup
php artisan api-docs:import openapi.yaml --validate --backup
```

```bash
# Merge with existing specification
php artisan api-docs:import openapi.json --merge
```

### Generate Static Files

Generate static HTML documentation files:

```bash
# Generate static files for all themes
php artisan api-docs:static
```

```bash
# Generate specific themes
php artisan api-docs:static --themes=swagger,redoc
```

```bash
# Generate with custom output path
php artisan api-docs:static --output=/path/to/output
```


```bash
# Generate minified HTML
php artisan api-docs:static --minify
```

```bash
# Generate with custom base URL
php artisan api-docs:static --base-url=https://docs.example.com
```

### Clean Up Files

Clean up generated documentation files:

```bash
# Clean all files (dry run)
php artisan api-docs:clean --all --dry-run
```

```bash
# Clean backup files older than 7 days
php artisan api-docs:clean --backups --older-than=7
```


```bash
# Clean cache files
php artisan api-docs:clean --cache
```


```bash
# Clean generated files
php artisan api-docs:clean --generated
```

### Check Status

View documentation status and statistics:

```bash
# Basic status
php artisan api-docs:status
```
```bash
# Detailed status with route analysis
php artisan api-docs:status --detailed

# Show route analysis only
php artisan api-docs:status --routes

# Show file information only
php artisan api-docs:status --files
```

### Publish Assets

Publish package files for customization:

```bash
# Publish all files
php artisan api-docs:publish --all

# Publish configuration only
php artisan api-docs:publish --config

# Publish views only
php artisan api-docs:publish --views

# Publish assets only
php artisan api-docs:publish --assets
```

### Get Help

Display help information:

```bash
php artisan api-docs:help
```

## Themes

### Swagger UI

The default theme using Swagger UI for interactive API documentation.

**Features:**
- Interactive API testing
- Request/response examples
- Schema visualization
- Authentication support

### ReDoc

Beautiful, responsive API documentation with ReDoc.

**Features:**
- Clean, modern design
- Three-panel layout
- Advanced search functionality
- Customizable themes

### RapiDoc

Modern API documentation with RapiDoc.

**Features:**
- Multiple layout options
- Built-in API testing
- Customizable styling
- Advanced filtering

### Custom Theme

Create your own custom theme by extending the base theme system.

## Advanced Usage

### Custom Route Detection

You can customize how routes are detected and processed:

```php
// In your configuration
'scan_routes' => [
    'prefix' => 'api',
    'exclude' => [
        'telescope',
        'horizon',
        'debugbar',
        '_ignition'
    ],
    'include_middleware' => [
        'api',
        'auth:api',
        'auth:sanctum'
    ]
]
```

### Security Configuration

Protect your documentation with authentication:

```php
'security' => [
    'enabled' => true,
    'middleware' => ['auth'],
    'allowed_ips' => ['127.0.0.1'],
    'basic_auth' => [
        'enabled' => true,
        'username' => env('API_DOCS_USERNAME'),
        'password' => env('API_DOCS_PASSWORD')
    ]
]
```

### Caching

Enable caching for better performance:

```php
'cache' => [
    'enabled' => true,
    'ttl' => 3600, // 1 hour
    'key_prefix' => 'api_docs',
    'store' => 'redis'
]
```

### Static File Generation

Generate static files for deployment:

```php
'static' => [
    'output_path' => storage_path('api-docs/static'),
    'base_url' => 'https://docs.example.com',
    'themes' => ['swagger', 'redoc'],
    'minify_html' => true,
    'include_assets' => true,
    'generate_sitemap' => true
]
```

## Testing

The package includes a comprehensive test suite:

```bash
# Run all tests
composer test
```

```bash
# Run tests with coverage
composer test-coverage
```

```bash
# Run code style checks
composer cs-check
```

```bash
# Fix code style issues
composer cs-fix
```

```bash
# Run all quality checks
composer test-all
```

### Using Test Factories

The package includes factory classes for testing:

```php
use XMultibyte\ApiDoc\Tests\Concerns\UsesFactories;

class MyTest extends TestCase
{
    use UsesFactories;

    public function test_something()
    {
        $spec = $this->createOpenApiSpec();
        $route = $this->createRoute('GET', 'api/users');

        // Your test logic here
    }
}
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details on how to contribute to this project.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [X-Multibyte](https://github.com/x-multibyte)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

If you discover any security related issues, please email security@x-multibyte.com instead of using the issue tracker.

For general support and questions, please use the [GitHub Discussions](https://github.com/x-multibyte/laravel-api-docs/discussions) or open an issue on [GitHub](https://github.com/x-multibyte/laravel-api-docs/issues).

## Roadmap

- [ ] GraphQL support
- [ ] API versioning support
- [ ] Advanced authentication schemes
- [ ] Custom annotation support
- [ ] Integration with popular API testing tools
- [ ] Multi-language documentation support
- [ ] Advanced caching strategies
- [ ] Real-time documentation updates
