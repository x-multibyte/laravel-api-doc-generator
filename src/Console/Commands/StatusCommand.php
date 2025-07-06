<?php

namespace XMultibyte\ApiDoc\Console\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class StatusCommand extends BaseCommand
{
    protected $signature = 'api-docs:status 
                            {--detailed : Show detailed information}
                            {--routes : Show route analysis}
                            {--files : Show file information}';

    protected $description = 'Show API documentation status and statistics';

    public function handle()
    {
        $this->displayHeader('API Documentation Status');

        $detailed = $this->option('detailed');
        $showRoutes = $this->option('routes');
        $showFiles = $this->option('files');

        // Basic status
        $this->displayBasicStatus();

        // Configuration status
        $this->displayConfigurationStatus();

        // Route analysis
        if ($showRoutes || $detailed) {
            $this->displayRouteAnalysis();
        }

        // File information
        if ($showFiles || $detailed) {
            $this->displayFileInformation();
        }

        // Health check
        $this->displayHealthCheck();

        return 0;
    }

    protected function displayBasicStatus()
    {
        $this->line('<fg=white;options=bold>📊 Basic Information</>');
        $this->line('');

        // API Documentation configuration
        $title = config('api-docs.title', 'Not configured');
        $version = config('api-docs.version', 'Not configured');
        $description = config('api-docs.description', 'Not configured');

        $this->line("  📝 Title: {$title}");
        $this->line("  🔢 Version: {$version}");
        $this->line("  📄 Description: {$description}");
        $this->line('');

        // Available themes
        $themes = config('api-docs.available_themes', []);
        $defaultTheme = config('api-docs.default_theme', 'swagger');
        
        $this->line("  🎨 Default Theme: {$defaultTheme}");
        $this->line("  🎭 Available Themes: " . implode(', ', array_keys($themes)));
        $this->line('');
    }

    protected function displayConfigurationStatus()
    {
        $this->line('<fg=white;options=bold>⚙️  Configuration Status</>');
        $this->line('');

        // Route configuration
        $routePrefix = config('api-docs.route_prefix', 'api-docs');
        $middleware = config('api-docs.middleware', []);
        
        $this->line("  🛣️  Route Prefix: /{$routePrefix}");
        $this->line("  🛡️  Middleware: " . (empty($middleware) ? 'None' : implode(', ', $middleware)));

        // Scan configuration
        $scanPrefix = config('api-docs.scan_routes.prefix', 'api');
        $excludePatterns = config('api-docs.scan_routes.exclude', []);
        
        $this->line("  🔍 Scan Prefix: /{$scanPrefix}");
        $this->line("  ❌ Exclude Patterns: " . (empty($excludePatterns) ? 'None' : implode(', ', $excludePatterns)));

        // OpenAPI configuration
        $openApiVersion = config('api-docs.openapi.version', '3.0.3');
        $servers = config('api-docs.openapi.servers', []);
        
        $this->line("  📋 OpenAPI Version: {$openApiVersion}");
        $this->line("  🌐 Servers: " . count($servers) . " configured");
        $this->line('');
    }

    protected function displayRouteAnalysis()
    {
        $this->line('<fg=white;options=bold>🛣️  Route Analysis</>');
        $this->line('');

        // Get all routes
        $allRoutes = collect(Route::getRoutes());
        $totalRoutes = $allRoutes->count();

        // Filter API routes
        $scanPrefix = config('api-docs.scan_routes.prefix', 'api');
        $excludePatterns = config('api-docs.scan_routes.exclude', []);
        
        $apiRoutes = $allRoutes->filter(function ($route) use ($scanPrefix, $excludePatterns) {
            $uri = $route->uri();
            
            if (!str_starts_with($uri, $scanPrefix)) {
                return false;
            }
            
            foreach ($excludePatterns as $pattern) {
                if (fnmatch($pattern, $uri)) {
                    return false;
                }
            }
            
            return true;
        });

        $apiRouteCount = $apiRoutes->count();

        // Analyze methods
        $methodCounts = [];
        $controllerCounts = [];
        $middlewareCounts = [];

        foreach ($apiRoutes as $route) {
            // Count methods
            foreach ($route->methods() as $method) {
                if ($method !== 'HEAD') {
                    $methodCounts[$method] = ($methodCounts[$method] ?? 0) + 1;
                }
            }

            // Count controllers
            $action = $route->getAction();
            if (isset($action['controller'])) {
                $controller = explode('@', $action['controller'])[0];
                $controllerName = class_basename($controller);
                $controllerCounts[$controllerName] = ($controllerCounts[$controllerName] ?? 0) + 1;
            }

            // Count middleware
            foreach ($route->middleware() as $middleware) {
                $middlewareCounts[$middleware] = ($middlewareCounts[$middleware] ?? 0) + 1;
            }
        }

        $this->line("  📊 Total Routes: {$totalRoutes}");
        $this->line("  🔗 API Routes: {$apiRouteCount}");
        $this->line("  📈 Coverage: " . round(($apiRouteCount / max($totalRoutes, 1)) * 100, 1) . "%");
        $this->line('');

        // Method distribution
        if (!empty($methodCounts)) {
            $this->line("  🔧 HTTP Methods:");
            arsort($methodCounts);
            foreach ($methodCounts as $method => $count) {
                $this->line("    {$method}: {$count}");
            }
            $this->line('');
        }

        // Top controllers
        if (!empty($controllerCounts)) {
            $this->line("  🎮 Top Controllers:");
            arsort($controllerCounts);
            $topControllers = array_slice($controllerCounts, 0, 5, true);
            foreach ($topControllers as $controller => $count) {
                $this->line("    {$controller}: {$count} routes");
            }
            $this->line('');
        }

        // Common middleware
        if (!empty($middlewareCounts)) {
            $this->line("  🛡️  Common Middleware:");
            arsort($middlewareCounts);
            $topMiddleware = array_slice($middlewareCounts, 0, 5, true);
            foreach ($topMiddleware as $middleware => $count) {
                $this->line("    {$middleware}: {$count} routes");
            }
            $this->line('');
        }
    }

    protected function displayFileInformation()
    {
        $this->line('<fg=white;options=bold>📁 File Information</>');
        $this->line('');

        $docsDir = storage_path('api-docs');
        $backupDir = storage_path('api-docs/backups');
        $cacheDir = storage_path('api-docs/cache');

        // Main documentation files
        $this->checkDirectory('Documentation Directory', $docsDir, [
            'openapi.json',
            'openapi.yaml',
            'imported_openapi.json',
            'imported_openapi.yaml'
        ]);

        // Backup files
        $this->checkDirectory('Backup Directory', $backupDir);

        // Cache files
        $this->checkDirectory('Cache Directory', $cacheDir);

        $this->line('');
    }

    protected function checkDirectory($name, $path, $expectedFiles = [])
    {
        $this->line("  📂 {$name}: {$path}");
        
        if (!File::exists($path)) {
            $this->line("    ❌ Directory does not exist");
            return;
        }

        $files = File::files($path);
        $directories = File::directories($path);
        $totalSize = 0;

        foreach ($files as $file) {
            $totalSize += $file->getSize();
        }

        $this->line("    📊 Files: " . count($files));
        $this->line("    📁 Subdirectories: " . count($directories));
        $this->line("    💾 Total Size: " . $this->formatBytes($totalSize));

        // Check expected files
        if (!empty($expectedFiles)) {
            foreach ($expectedFiles as $expectedFile) {
                $filePath = $path . '/' . $expectedFile;
                if (File::exists($filePath)) {
                    $size = File::size($filePath);
                    $modified = date('Y-m-d H:i:s', File::lastModified($filePath));
                    $this->line("    ✅ {$expectedFile} ({$this->formatBytes($size)}, modified: {$modified})");
                } else {
                    $this->line("    ❌ {$expectedFile} (missing)");
                }
            }
        }

        $this->line('');
    }

    protected function displayHealthCheck()
    {
        $this->line('<fg=white;options=bold>🏥 Health Check</>');
        $this->line('');

        $issues = [];
        $warnings = [];

        // Check configuration
        if (!config('api-docs.title')) {
            $issues[] = 'API title not configured';
        }

        if (!config('api-docs.version')) {
            $issues[] = 'API version not configured';
        }

        // Check routes
        $scanPrefix = config('api-docs.scan_routes.prefix', 'api');
        $apiRoutes = collect(Route::getRoutes())->filter(function ($route) use ($scanPrefix) {
            return str_starts_with($route->uri(), $scanPrefix);
        });

        if ($apiRoutes->isEmpty()) {
            $warnings[] = "No API routes found with prefix '/{$scanPrefix}'";
        }

        // Check directories
        $docsDir = storage_path('api-docs');
        if (!File::exists($docsDir)) {
            $warnings[] = 'Documentation directory does not exist';
        }

        // Check dependencies
        if (!class_exists('Symfony\Component\Yaml\Yaml')) {
            $issues[] = 'Symfony YAML component not installed (required for YAML export)';
        }

        // Display results
        if (empty($issues) && empty($warnings)) {
            $this->displaySuccess('All checks passed! 🎉');
        } else {
            if (!empty($issues)) {
                $this->line('  ❌ Issues:');
                foreach ($issues as $issue) {
                    $this->line("    • {$issue}");
                }
                $this->line('');
            }

            if (!empty($warnings)) {
                $this->line('  ⚠️  Warnings:');
                foreach ($warnings as $warning) {
                    $this->line("    • {$warning}");
                }
                $this->line('');
            }
        }

        // Recommendations
        $this->line('  💡 Recommendations:');
        $this->line('    • Run "php artisan api-docs:generate" to create documentation');
        $this->line('    • Visit /api-docs to view the documentation interface');
        $this->line('    • Use "php artisan api-docs:clean --dry-run" to check for cleanup opportunities');
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
