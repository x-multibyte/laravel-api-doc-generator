# Laravel API Documentation Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/x-multibyte/laravel-api-docs.svg?style=flat-square)](https://packagist.org/packages/x-multibyte/laravel-api-docs)
[![Total Downloads](https://img.shields.io/packagist/dt/x-multibyte/laravel-api-docs.svg?style=flat-square)](https://packagist.org/packages/x-multibyte/laravel-api-docs)
[![License](https://img.shields.io/packagist/l/x-multibyte/laravel-api-docs.svg?style=flat-square)](https://packagist.org/packages/x-multibyte/laravel-api-docs)

A powerful Laravel package that automatically generates beautiful API documentation from your Laravel routes with support for multiple UI themes and OpenAPI 3+ specifications.

## ✨ Features

- 🎨 **Multiple UI Themes**: Swagger UI, ReDoc, RapiDoc, and Custom themes
- 📋 **OpenAPI 3+ Support**: Full compliance with OpenAPI Specification 3.0.3
- 🔄 **Import/Export**: Support for JSON and YAML formats
- 🛣️ **Smart Route Scanning**: Automatically discovers and analyzes API routes
- 🎯 **Flexible Configuration**: Highly customizable with extensive options
- 🧹 **Management Tools**: Complete CLI toolkit for documentation management
- 🔍 **Validation**: Built-in OpenAPI specification validation
- 💾 **Backup System**: Automatic backup and restore functionality
- 📄 **Static Generation**: Generate standalone HTML documentation files
- 🚀 **Easy Integration**: Simple installation and setup process

## 📸 Screenshots

### Swagger UI Theme
Experience the classic API documentation interface with interactive testing capabilities.

![Swagger UI Theme](https://via.placeholder.com/800x400/1f2937/ffffff?text=Swagger+UI+Theme)

### ReDoc Theme  
Modern, responsive documentation layout with excellent readability.

![ReDoc Theme](https://via.placeholder.com/800x400/2563eb/ffffff?text=ReDoc+Theme)

### RapiDoc Theme
Fast, lightweight documentation viewer with customizable styling.

![RapiDoc Theme](https://via.placeholder.com/800x400/059669/ffffff?text=RapiDoc+Theme)

### Custom Theme
Fully customizable theme that you can modify to match your brand.

![Custom Theme](https://via.placeholder.com/800x400/7c3aed/ffffff?text=Custom+Theme)

> **Note**: Replace these placeholder images with actual screenshots of your themes in action. 
> Recommended image size: 800x400px or higher resolution for better display.

## 🚀 Installation

You can install the package via Composer:

\`\`\`bash
composer require x-multibyte/laravel-api-docs
\`\`\`

### Laravel Auto-Discovery

The package will automatically register itself via Laravel's package auto-discovery feature.

### Manual Registration (Laravel < 5.5)

If you're using Laravel < 5.5, add the service provider to your `config/app.php`:

\`\`\`php
'providers' => [
    // ...
    XMultibyte\ApiDoc\ApiDocsServiceProvider::class,
],
\`\`\`

## ⚙️ Configuration

Publish the configuration file:

\`\`\`bash
php artisan api-docs:publish --config
\`\`\`

This will create a `config/api-docs.php` file where you can customize all settings:

\`\`\`php
<?php

return [
    'title' => 'My API Documentation',
    'description' => 'Comprehensive API documentation for my application',
    'version' => '1.0.0',
    
    'route_prefix' => 'api-docs',
    'middleware' => ['web'],
    
    'default_theme' => 'swagger',
    'available_themes' => [
        'swagger' => 'Swagger UI',
        'redoc' => 'ReDoc',
        'rapidoc' => 'RapiDoc',
        'custom' => 'Custom Theme',
    ],
    
    // ... more configuration options
];
\`\`\`

## 🎯 Quick Start

### 1. Publish Assets

\`\`\`bash
php artisan api-docs:publish --all
\`\`\`

### 2. Generate Documentation

\`\`\`bash
php artisan api-docs:generate --validate
\`\`\`

### 3. View Documentation

Visit `http://your-app.com/api-docs` in your browser to see your API documentation.

## 📚 Usage

### Web Interface

Once installed, you can access the documentation interface at `/api-docs` (or your configured route prefix). The interface provides:

- **Theme Switching**: Toggle between different UI themes
- **Export Options**: Download documentation in JSON or YAML format
- **Import Functionality**: Upload and import OpenAPI specifications
- **Interactive Testing**: Test API endpoints directly from the documentation

### Command Line Interface

The package includes a comprehensive CLI toolkit:

#### Generate Documentation

\`\`\`bash
# Basic generation
php artisan api-docs:generate

# Generate with validation
php artisan api-docs:generate --validate

# Generate both JSON and YAML formats
php artisan api-docs:generate --format=both

# Include specific routes only
php artisan api-docs:generate --routes="api/users/*,api/posts/*"

# Exclude certain routes
php artisan api-docs:generate --exclude="api/admin/*"

# Minify JSON output
php artisan api-docs:generate --minify --force
\`\`\`

#### Generate Static Documentation

\`\`\`bash
# Generate static HTML files
php artisan api-docs:static

# Generate specific themes
php artisan api-docs:static --themes=swagger,redoc

# Specify output directory
php artisan api-docs:static --output=/var/www/docs

# Minify HTML and skip assets
php artisan api-docs:static --minify --no-assets
\`\`\`

#### Import Documentation

\`\`\`bash
# Import from file
php artisan api-docs:import openapi.json

# Import with validation and backup
php artisan api-docs:import spec.yaml --validate --backup

# Merge with existing specification
php artisan api-docs:import external.json --merge
\`\`\`

#### Status and Monitoring

\`\`\`bash
# Check documentation status
php artisan api-docs:status

# Detailed analysis
php artisan api-docs:status --detailed

# Route analysis
php artisan api-docs:status --routes --files
\`\`\`

#### Cleanup and Maintenance

\`\`\`bash
# Preview cleanup (dry run)
php artisan api-docs:clean --all --dry-run

# Clean old backup files
php artisan api-docs:clean --backups --older-than=30

# Clean cache and generated files
php artisan api-docs:clean --cache --generated
\`\`\`

#### Help and Documentation

\`\`\`bash
# Show all available commands
php artisan api-docs:help
\`\`\`

## 🎨 Themes

### Swagger UI
The classic API documentation interface with interactive testing capabilities.

### ReDoc
A modern, responsive documentation layout with excellent readability.

### RapiDoc
A fast, lightweight documentation viewer with customizable styling.

### Custom Theme
A fully customizable theme that you can modify to match your brand.

## 📄 Static Documentation Generation

Generate standalone HTML documentation files that can be deployed to any static hosting service:

\`\`\`bash
# Generate static files
php artisan api-docs:static

# Output structure:
# output-directory/
# ├── index.html              # Main page
# ├── swagger.html            # Swagger UI theme
# ├── redoc.html              # ReDoc theme
# ├── rapidoc.html            # RapiDoc theme
# ├── custom.html             # Custom theme
# ├── openapi.json            # OpenAPI spec (JSON)
# ├── openapi.yaml            # OpenAPI spec (YAML)
# ├── sitemap.xml             # Sitemap
# └── assets/                 # CSS, JS, images
\`\`\`

### Deployment Options

- **GitHub Pages**: Upload to `gh-pages` branch
- **Netlify**: Drag and drop the output folder
- **Vercel**: Deploy with `vercel --prod`
- **AWS S3**: Sync to S3 bucket with CloudFront
- **Any CDN**: Upload files to your preferred CDN

## 🔧 Advanced Configuration

### Route Scanning

Configure which routes to include in your documentation:

\`\`\`php
'scan_routes' => [
    'prefix' => 'api',
    'exclude' => [
        'telescope',
        'horizon',
        'nova-api',
    ],
],
\`\`\`

### OpenAPI Configuration

Customize your OpenAPI specification:

\`\`\`php
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
    ],
],
\`\`\`

### Static Generation Configuration

\`\`\`php
'static' => [
    'enabled' => true,
    'output_path' => public_path('api-docs-static'),
    'base_url' => env('API_DOCS_STATIC_BASE_URL', '/api-docs-static'),
    'themes' => ['swagger', 'redoc', 'rapidoc', 'custom'],
    'include_assets' => true,
    'minify_html' => false,
    'generate_sitemap' => true,
],
\`\`\`

### Middleware Configuration

Protect your documentation with middleware:

\`\`\`php
'middleware' => ['web', 'auth', 'admin'],
\`\`\`

## 🎯 Customization

### Custom Views

Publish and customize the view templates:

\`\`\`bash
php artisan api-docs:publish --views
\`\`\`

Views will be published to `resources/views/vendor/api-docs/` where you can customize:

- `index.blade.php` - Main documentation page
- `themes/swagger.blade.php` - Swagger UI theme
- `themes/redoc.blade.php` - ReDoc theme
- `themes/rapidoc.blade.php` - RapiDoc theme
- `themes/custom.blade.php` - Custom theme

### Custom Styling

Publish assets and customize the styling:

\`\`\`bash
php artisan api-docs:publish --assets
\`\`\`

Assets will be published to `public/vendor/api-docs/` where you can modify CSS and JavaScript files.

## 📖 API Reference

### ApiDocsGenerator Class

The main generator class provides methods for:

\`\`\`php
// Generate OpenAPI specification
$spec = app('api-docs')->generate();

// Export to JSON
$json = app('api-docs')->exportToJson();

// Export to YAML
$yaml = app('api-docs')->exportToYaml();

// Import from JSON
app('api-docs')->importFromJson($jsonString);

// Import from YAML
app('api-docs')->importFromYaml($yamlString);
\`\`\`

### Route Helpers

Access documentation routes programmatically:

\`\`\`php
// Get documentation URL
$url = route('api-docs.index');

// Get specification URLs
$jsonUrl = route('api-docs.spec.json');
$yamlUrl = route('api-docs.spec.yaml');

// Get theme URLs
$swaggerUrl = route('api-docs.swagger');
$redocUrl = route('api-docs.redoc');
$rapidocUrl = route('api-docs.rapidoc');
\`\`\`

## 🧪 Testing

Run the test suite:

\`\`\`bash
composer test
\`\`\`

Run tests with coverage:

\`\`\`bash
composer test-coverage
\`\`\`

## 📊 Performance

### Caching

The package automatically caches generated documentation to improve performance. Cache files are stored in `storage/api-docs/cache/`.

### Optimization Tips

1. **Use Route Caching**: Run `php artisan route:cache` in production
2. **Configure Exclusions**: Exclude unnecessary routes to speed up generation
3. **Enable Minification**: Use `--minify` flag for smaller JSON files
4. **Regular Cleanup**: Use `api-docs:clean` to remove old files

## 🔒 Security

### Access Control

Protect your documentation with middleware:

\`\`\`php
'middleware' => ['web', 'auth:admin'],
\`\`\`

### Environment Configuration

Use environment variables for sensitive configuration:

\`\`\`env
API_DOCS_TITLE="My API Documentation"
API_DOCS_VERSION="1.0.0"
API_DOCS_DESCRIPTION="Internal API Documentation"
\`\`\`

## 🚀 Deployment

### Production Setup

1. **Publish Configuration**:
   \`\`\`bash
   php artisan api-docs:publish --config
   \`\`\`

2. **Generate Documentation**:
   \`\`\`bash
   php artisan api-docs:generate --validate --minify
   \`\`\`

3. **Generate Static Files** (optional):
   \`\`\`bash
   php artisan api-docs:static --minify
   \`\`\`

4. **Configure Caching**:
   \`\`\`bash
   php artisan config:cache
   php artisan route:cache
   \`\`\`

### CI/CD Integration

Add to your deployment script:

\`\`\`bash
#!/bin/bash
php artisan api-docs:generate --validate --force
php artisan api-docs:static --force
php artisan api-docs:clean --cache --older-than=7
\`\`\`

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Setup

1. **Clone the repository**:
   \`\`\`bash
   git clone https://github.com/x-multibyte/laravel-api-docs.git
   cd laravel-api-docs
   \`\`\`

2. **Install dependencies**:
   \`\`\`bash
   composer install
   \`\`\`

3. **Run tests**:
   \`\`\`bash
   composer test
   \`\`\`

### Coding Standards

- Follow PSR-12 coding standards
- Write comprehensive tests for new features
- Update documentation for any changes
- Use meaningful commit messages

## 📝 Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.

## 🔐 Security

If you discover any security-related issues, please email security@x-multibyte.com instead of using the issue tracker.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🙏 Credits

- **Author**: [X-Multibyte](https://github.com/x-multibyte)
- **Contributors**: [All Contributors](../../contributors)

### Built With

- [Laravel](https://laravel.com) - The PHP Framework
- [Swagger UI](https://swagger.io/tools/swagger-ui/) - API Documentation UI
- [ReDoc](https://github.com/Redocly/redoc) - OpenAPI Documentation
- [RapiDoc](https://mrin9.github.io/RapiDoc/) - API Documentation Tool
- [Symfony YAML](https://symfony.com/doc/current/components/yaml.html) - YAML Parser

## 🌟 Support

- ⭐ **Star this repository** if you find it helpful
- 🐛 **Report bugs** via [GitHub Issues](https://github.com/x-multibyte/laravel-api-docs/issues)
- 💡 **Request features** via [GitHub Discussions](https://github.com/x-multibyte/laravel-api-docs/discussions)
- 📖 **Read the documentation** at [docs.x-multibyte.com](https://docs.x-multibyte.com)

## 📞 Community

- **Discord**: [Join our Discord server](https://discord.gg/x-multibyte)
- **Twitter**: [@XMultibyte](https://twitter.com/XMultibyte)
- **Blog**: [blog.x-multibyte.com](https://blog.x-multibyte.com)

---

<p align="center">
  <strong>Made with ❤️ by X-Multibyte</strong>
</p>

<p align="center">
  <a href="https://laravel.com">
    <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  </a>
  <a href="https://php.net">
    <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  </a>
  <a href="https://swagger.io">
    <img src="https://img.shields.io/badge/OpenAPI-6BA539?style=for-the-badge&logo=openapi-initiative&logoColor=white" alt="OpenAPI">
  </a>
</p>
