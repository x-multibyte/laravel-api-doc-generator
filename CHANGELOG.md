# Changelog

All notable changes to `laravel-api-docs/generator` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of Laravel API Documentation Generator
- Multiple UI themes (Swagger UI, ReDoc, RapiDoc, Custom)
- OpenAPI 3+ specification support
- Comprehensive CLI toolkit
- Import/Export functionality for JSON and YAML
- Smart route scanning and analysis
- Built-in validation system
- Backup and restore functionality

## [1.0.0] - 2024-01-15

### Added
- **Core Features**
  - Automatic API documentation generation from Laravel routes
  - Support for OpenAPI Specification 3.0.3
  - Multiple documentation themes with easy switching
  - Web-based documentation interface

- **UI Themes**
  - Swagger UI integration with interactive testing
  - ReDoc theme for modern documentation layout
  - RapiDoc theme for fast, lightweight viewing
  - Custom theme with full customization support

- **Command Line Interface**
  - `api-docs:generate` - Generate documentation with various options
  - `api-docs:import` - Import OpenAPI specifications
  - `api-docs:export` - Export documentation in multiple formats
  - `api-docs:clean` - Cleanup and maintenance tools
  - `api-docs:status` - Status monitoring and health checks
  - `api-docs:publish` - Publish package assets and configuration
  - `api-docs:help` - Comprehensive help system

- **Configuration System**
  - Flexible route scanning with include/exclude patterns
  - Customizable OpenAPI server and security configurations
  - Theme selection and UI customization options
  - Middleware and access control settings

- **Import/Export Features**
  - JSON format support with optional minification
  - YAML format support with proper formatting
  - Specification validation before import/export
  - Backup creation during import operations
  - Merge functionality for combining specifications

- **Advanced Features**
  - Smart route parameter detection and documentation
  - Automatic HTTP method and response code generation
  - Controller and middleware analysis
  - Tag-based organization of API endpoints
  - Security scheme detection and documentation

- **Management Tools**
  - File cleanup with age-based filtering
  - Backup management system
  - Cache management and optimization
  - Detailed status reporting and analytics
  - Health check system with recommendations

### Technical Details
- **Requirements**: PHP 8.1+, Laravel 10.0+
- **Dependencies**: Symfony YAML component for YAML processing
- **Architecture**: Service provider pattern with dependency injection
- **Storage**: File-based storage in Laravel storage directory
- **Caching**: Automatic caching for improved performance

### Documentation
- Comprehensive README with installation and usage instructions
- Detailed configuration documentation
- Command-line reference guide
- Customization and theming guide
- Contributing guidelines and development setup

### Testing
- Unit tests for core functionality
- Feature tests for CLI commands
- Integration tests for web interface
- Validation tests for OpenAPI compliance

---

## Version History

### Pre-release Development

#### [0.9.0] - 2024-01-10
- Beta release with core functionality
- Initial theme implementations
- Basic CLI command structure

#### [0.8.0] - 2024-01-05
- Alpha release for testing
- Route scanning implementation
- OpenAPI specification generation

#### [0.7.0] - 2024-01-01
- Development preview
- Service provider setup
- Configuration system design

---

## Upgrade Guide

### From 0.x to 1.0.0

This is the first stable release. If you were using pre-release versions:

1. **Update Composer**:
   \`\`\`bash
   composer update laravel-api-docs/generator
   \`\`\`

2. **Republish Configuration**:
   \`\`\`bash
   php artisan api-docs:publish --config --force
   \`\`\`

3. **Regenerate Documentation**:
   \`\`\`bash
   php artisan api-docs:generate --validate
   \`\`\`

---

## Breaking Changes

### 1.0.0
- Initial stable release - no breaking changes from pre-release versions
- Configuration structure finalized
- CLI command signatures stabilized

---

## Security Updates

### 1.0.0
- Implemented secure file handling for import/export operations
- Added validation for uploaded OpenAPI specifications
- Secure middleware configuration for documentation access
- Protection against path traversal in file operations

---

## Performance Improvements

### 1.0.0
- Optimized route scanning algorithm
- Implemented caching for generated specifications
- Reduced memory usage during large API processing
- Improved file I/O operations for better performance

---

## Bug Fixes

### 1.0.0
- Fixed route parameter parsing for complex patterns
- Resolved middleware detection issues
- Corrected OpenAPI specification validation
- Fixed theme switching functionality
- Resolved file permission issues in storage operations

---

## Deprecated Features

Currently no deprecated features. All functionality is stable and supported.

---

## Planned Features

### 2.0.0 (Planned)
- API versioning support
- Enhanced customization options
- Additional export formats (Postman, Insomnia)
- Real-time documentation updates
- Integration with Laravel Sanctum/Passport
- Multi-language support

### 1.1.0 (Planned)
- Performance optimizations
- Additional validation rules
- Enhanced error reporting
- More customization options for themes

---

## Contributors

Special thanks to all contributors who made this release possible:

- [Your Name](https://github.com/yourusername) - Lead Developer
- [Contributor Name](https://github.com/contributor) - Feature Development
- [Another Contributor](https://github.com/another) - Testing and QA

---

## Support

For support and questions:
- 📖 [Documentation](https://docs.laravel-api-docs.com)
- 🐛 [Issue Tracker](https://github.com/laravel-api-docs/generator/issues)
- 💬 [Discussions](https://github.com/laravel-api-docs/generator/discussions)
- 💬 [Discord](https://discord.gg/laravel-api-docs)
