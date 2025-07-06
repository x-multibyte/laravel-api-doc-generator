# Contributing to Laravel API Documentation Generator

Thank you for considering contributing to the Laravel API Documentation Generator! This document outlines the process for contributing to this project.

## 🤝 Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## 🚀 Getting Started

### Prerequisites

- PHP 8.1 or higher
- Laravel 10.0 or higher
- Composer
- Git

### Development Setup

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   \`\`\`bash
   git clone https://github.com/YOUR-USERNAME/laravel-api-docs-generator.git
   cd laravel-api-docs-generator
   \`\`\`

3. **Install dependencies**:
   \`\`\`bash
   composer install
   \`\`\`

4. **Create a branch** for your feature or bugfix:
   \`\`\`bash
   git checkout -b feature/your-feature-name
   \`\`\`

## 🐛 Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When creating a bug report, include:

- **Clear title** and description
- **Steps to reproduce** the issue
- **Expected behavior** vs actual behavior
- **Environment details** (PHP version, Laravel version, etc.)
- **Code samples** or error messages if applicable

### Bug Report Template

\`\`\`markdown
**Describe the bug**
A clear and concise description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

**Expected behavior**
A clear and concise description of what you expected to happen.

**Environment:**
- PHP Version: [e.g. 8.1]
- Laravel Version: [e.g. 10.0]
- Package Version: [e.g. 1.0.0]

**Additional context**
Add any other context about the problem here.
\`\`\`

## 💡 Suggesting Features

Feature requests are welcome! Please provide:

- **Clear description** of the feature
- **Use case** and motivation
- **Possible implementation** approach (if you have ideas)
- **Examples** of similar features in other tools

## 🔧 Development Guidelines

### Coding Standards

- Follow **PSR-12** coding standards
- Use **meaningful variable and method names**
- Write **comprehensive PHPDoc comments**
- Keep methods **focused and small**
- Use **type hints** wherever possible

### Testing

- Write tests for all new features
- Ensure all existing tests pass
- Aim for high test coverage
- Use descriptive test method names

\`\`\`bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Run specific test
./vendor/bin/phpunit tests/Feature/GenerateDocsCommandTest.php
\`\`\`

### Code Style

We use PHP CS Fixer to maintain consistent code style:

\`\`\`bash
# Check code style
composer cs-check

# Fix code style
composer cs-fix
\`\`\`

## 📝 Pull Request Process

1. **Update documentation** if needed
2. **Add tests** for new functionality
3. **Ensure all tests pass**
4. **Update CHANGELOG.md** with your changes
5. **Create a pull request** with:
   - Clear title and description
   - Reference to related issues
   - Screenshots (if UI changes)

### Pull Request Template

\`\`\`markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Tests pass locally
- [ ] New tests added
- [ ] Manual testing completed

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
\`\`\`

## 📚 Documentation

- Update README.md for new features
- Add inline code comments
- Update configuration examples
- Include usage examples

## 🏗️ Project Structure

\`\`\`
src/
├── Console/Commands/     # Artisan commands
├── Http/Controllers/     # Web controllers
├── ApiDocsGenerator.php  # Main generator class
└── ApiDocsServiceProvider.php

config/
└── api-docs.php         # Configuration file

resources/
├── views/               # Blade templates
└── assets/              # CSS/JS assets

tests/
├── Feature/             # Feature tests
└── Unit/                # Unit tests
\`\`\`

## 🧪 Testing Strategy

### Unit Tests
Test individual classes and methods in isolation.

### Feature Tests
Test complete workflows and integrations.

### Browser Tests
Test the web interface functionality.

## 📋 Commit Guidelines

Use conventional commit messages:

- `feat:` new features
- `fix:` bug fixes
- `docs:` documentation changes
- `style:` code style changes
- `refactor:` code refactoring
- `test:` test additions/changes
- `chore:` maintenance tasks

Examples:
\`\`\`
feat: add YAML export functionality
fix: resolve route scanning issue with middleware
docs: update installation instructions
\`\`\`

## 🎯 Areas for Contribution

We especially welcome contributions in these areas:

- **New UI themes** and customization options
- **Performance optimizations** for large APIs
- **Additional export formats** (Postman, Insomnia, etc.)
- **Enhanced route analysis** and documentation generation
- **Internationalization** support
- **Integration** with other Laravel packages

## 🆘 Getting Help

If you need help with development:

- Check existing [GitHub Issues](https://github.com/laravel-api-docs/generator/issues)
- Join our [Discord server](https://discord.gg/laravel-api-docs)
- Ask questions in [GitHub Discussions](https://github.com/laravel-api-docs/generator/discussions)

## 🏆 Recognition

Contributors will be:

- Listed in the README.md credits section
- Mentioned in release notes
- Invited to join the core team (for significant contributions)

Thank you for contributing to Laravel API Documentation Generator! 🎉
