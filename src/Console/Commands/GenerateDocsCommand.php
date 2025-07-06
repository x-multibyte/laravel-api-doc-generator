<?php

namespace LaravelApiDocs\Console\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class GenerateDocsCommand extends BaseCommand
{
    protected $signature = 'api-docs:generate 
                            {--format=json : Output format (json, yaml, both)}
                            {--output= : Output directory path}
                            {--routes= : Specific routes to include (comma-separated)}
                            {--exclude= : Routes to exclude (comma-separated)}
                            {--force : Overwrite existing files}
                            {--minify : Minify JSON output}
                            {--validate : Validate generated specification}';

    protected $description = 'Generate API documentation from Laravel routes';

    public function handle()
    {
        $this->displayHeader('API Documentation Generator');

        $format = $this->option('format');
        $outputPath = $this->option('output') ?: storage_path('api-docs');
        $specificRoutes = $this->option('routes') ? explode(',', $this->option('routes')) : null;
        $excludeRoutes = $this->option('exclude') ? explode(',', $this->option('exclude')) : [];
        $force = $this->option('force');
        $minify = $this->option('minify');
        $validate = $this->option('validate');

        // Ensure output directory exists
        if (!File::exists($outputPath)) {
            File::makeDirectory($outputPath, 0755, true);
            $this->displayInfo("Created output directory: {$outputPath}");
        }

        // Scan routes
        $this->displayInfo('Scanning Laravel routes...');
        $routes = $this->scanRoutes($specificRoutes, $excludeRoutes);
        $this->displaySuccess("Found {$routes->count()} API routes");

        // Generate documentation
        $this->displayInfo('Generating API specification...');
        $progressBar = $this->createProgressBar($routes->count());
        $progressBar->start();

        $spec = $this->generator->generate();
        $progressBar->finish();
        $this->line('');

        // Validate if requested
        if ($validate) {
            $this->displayInfo('Validating OpenAPI specification...');
            $validationResult = $this->validateSpecification($spec);
            if ($validationResult['valid']) {
                $this->displaySuccess('Specification is valid');
            } else {
                $this->displayError('Specification validation failed:');
                foreach ($validationResult['errors'] as $error) {
                    $this->line("  - {$error}");
                }
                if (!$this->confirm('Continue with invalid specification?')) {
                    return 1;
                }
            }
        }

        // Generate output files
        $generated = [];
        
        if (in_array($format, ['json', 'both'])) {
            $jsonFile = $this->generateJsonFile($spec, $outputPath, $force, $minify);
            if ($jsonFile) {
                $generated[] = $jsonFile;
            }
        }

        if (in_array($format, ['yaml', 'both'])) {
            $yamlFile = $this->generateYamlFile($spec, $outputPath, $force);
            if ($yamlFile) {
                $generated[] = $yamlFile;
            }
        }

        // Display results
        $this->line('');
        $this->displaySuccess('Documentation generated successfully!');
        $this->line('');
        $this->line('<fg=white;options=bold>Generated files:</>');
        foreach ($generated as $file) {
            $this->line("  📄 {$file}");
        }

        // Display statistics
        $this->displayStatistics($spec);

        return 0;
    }

    protected function scanRoutes($specificRoutes = null, $excludeRoutes = [])
    {
        $routes = collect(Route::getRoutes())->filter(function ($route) use ($specificRoutes, $excludeRoutes) {
            $uri = $route->uri();
            
            // Check specific routes filter
            if ($specificRoutes) {
                $match = false;
                foreach ($specificRoutes as $pattern) {
                    if (fnmatch(trim($pattern), $uri)) {
                        $match = true;
                        break;
                    }
                }
                if (!$match) return false;
            }

            // Check exclude filter
            foreach ($excludeRoutes as $pattern) {
                if (fnmatch(trim($pattern), $uri)) {
                    return false;
                }
            }

            // Default API route filtering
            $prefix = config('api-docs.scan_routes.prefix', 'api');
            return str_starts_with($uri, $prefix);
        });

        return $routes;
    }

    protected function generateJsonFile($spec, $outputPath, $force, $minify)
    {
        $filename = 'openapi.json';
        $filepath = $outputPath . '/' . $filename;

        if (File::exists($filepath) && !$force) {
            if (!$this->confirm("File {$filename} already exists. Overwrite?")) {
                $this->displayWarning("Skipped {$filename}");
                return null;
            }
        }

        $json = $minify 
            ? json_encode($spec, JSON_UNESCAPED_SLASHES)
            : json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        File::put($filepath, $json);
        $this->displaySuccess("Generated {$filename}");

        return $filepath;
    }

    protected function generateYamlFile($spec, $outputPath, $force)
    {
        $filename = 'openapi.yaml';
        $filepath = $outputPath . '/' . $filename;

        if (File::exists($filepath) && !$force) {
            if (!$this->confirm("File {$filename} already exists. Overwrite?")) {
                $this->displayWarning("Skipped {$filename}");
                return null;
            }
        }

        $yaml = $this->generator->exportToYaml();
        File::put($filepath, $yaml);
        $this->displaySuccess("Generated {$filename}");

        return $filepath;
    }

    protected function validateSpecification($spec)
    {
        $errors = [];
        
        // Basic validation
        if (!isset($spec['openapi'])) {
            $errors[] = 'Missing OpenAPI version';
        }

        if (!isset($spec['info']['title'])) {
            $errors[] = 'Missing API title';
        }

        if (!isset($spec['info']['version'])) {
            $errors[] = 'Missing API version';
        }

        if (!isset($spec['paths']) || empty($spec['paths'])) {
            $errors[] = 'No API paths found';
        }

        // Validate paths
        if (isset($spec['paths'])) {
            foreach ($spec['paths'] as $path => $pathItem) {
                if (!is_array($pathItem)) {
                    $errors[] = "Invalid path item for {$path}";
                    continue;
                }

                foreach ($pathItem as $method => $operation) {
                    if (!in_array($method, ['get', 'post', 'put', 'patch', 'delete', 'options', 'head'])) {
                        continue;
                    }

                    if (!isset($operation['responses'])) {
                        $errors[] = "Missing responses for {$method} {$path}";
                    }
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    protected function displayStatistics($spec)
    {
        $this->line('');
        $this->line('<fg=white;options=bold>Statistics:</>');
        
        $pathCount = isset($spec['paths']) ? count($spec['paths']) : 0;
        $operationCount = 0;
        $tagCount = 0;
        $tags = [];

        if (isset($spec['paths'])) {
            foreach ($spec['paths'] as $pathItem) {
                foreach ($pathItem as $method => $operation) {
                    if (in_array($method, ['get', 'post', 'put', 'patch', 'delete', 'options', 'head'])) {
                        $operationCount++;
                        if (isset($operation['tags'])) {
                            $tags = array_merge($tags, $operation['tags']);
                        }
                    }
                }
            }
        }

        $tags = array_unique($tags);
        $tagCount = count($tags);

        $this->line("  📊 Paths: {$pathCount}");
        $this->line("  🔧 Operations: {$operationCount}");
        $this->line("  🏷️  Tags: {$tagCount}");
        
        if ($tagCount > 0) {
            $this->line("  📋 Available tags: " . implode(', ', $tags));
        }
    }
}
