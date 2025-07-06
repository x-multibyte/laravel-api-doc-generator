<?php

namespace LaravelApiDocs\Console\Commands;

use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class ImportDocsCommand extends BaseCommand
{
    protected $signature = 'api-docs:import 
                            {file : Path to OpenAPI specification file}
                            {--validate : Validate specification before import}
                            {--backup : Create backup of current specification}
                            {--merge : Merge with existing specification}';

    protected $description = 'Import OpenAPI specification from JSON or YAML file';

    public function handle()
    {
        $this->displayHeader('API Documentation Importer');

        $filePath = $this->argument('file');
        $validate = $this->option('validate');
        $backup = $this->option('backup');
        $merge = $this->option('merge');

        // Check if file exists
        if (!File::exists($filePath)) {
            $this->displayError("File not found: {$filePath}");
            return 1;
        }

        // Determine file format
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($extension, ['json', 'yaml', 'yml'])) {
            $this->displayError('Unsupported file format. Only JSON and YAML files are supported.');
            return 1;
        }

        $this->displayInfo("Reading {$extension} file: {$filePath}");

        // Read and parse file
        try {
            $content = File::get($filePath);
            
            if ($extension === 'json') {
                $spec = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON: ' . json_last_error_msg());
                }
            } else {
                $spec = Yaml::parse($content);
            }
        } catch (\Exception $e) {
            $this->displayError("Failed to parse file: {$e->getMessage()}");
            return 1;
        }

        $this->displaySuccess('File parsed successfully');

        // Validate specification
        if ($validate) {
            $this->displayInfo('Validating OpenAPI specification...');
            $validationResult = $this->validateSpecification($spec);
            
            if (!$validationResult['valid']) {
                $this->displayError('Specification validation failed:');
                foreach ($validationResult['errors'] as $error) {
                    $this->line("  - {$error}");
                }
                
                if (!$this->confirm('Continue with invalid specification?')) {
                    return 1;
                }
            } else {
                $this->displaySuccess('Specification is valid');
            }
        }

        // Create backup if requested
        if ($backup) {
            $this->createBackup();
        }

        // Import specification
        if ($merge) {
            $this->mergeSpecification($spec);
        } else {
            $this->importSpecification($spec);
        }

        $this->displaySuccess('API specification imported successfully!');
        $this->displayImportStatistics($spec);

        return 0;
    }

    protected function validateSpecification($spec)
    {
        $errors = [];
        
        // Check required OpenAPI fields
        if (!isset($spec['openapi'])) {
            $errors[] = 'Missing required field: openapi';
        } elseif (!preg_match('/^3\.\d+\.\d+$/', $spec['openapi'])) {
            $errors[] = 'Invalid OpenAPI version. Must be 3.x.x format';
        }

        if (!isset($spec['info'])) {
            $errors[] = 'Missing required field: info';
        } else {
            if (!isset($spec['info']['title'])) {
                $errors[] = 'Missing required field: info.title';
            }
            if (!isset($spec['info']['version'])) {
                $errors[] = 'Missing required field: info.version';
            }
        }

        // Validate paths structure
        if (isset($spec['paths'])) {
            foreach ($spec['paths'] as $path => $pathItem) {
                if (!str_starts_with($path, '/')) {
                    $errors[] = "Path must start with '/': {$path}";
                }

                if (is_array($pathItem)) {
                    foreach ($pathItem as $method => $operation) {
                        $validMethods = ['get', 'post', 'put', 'patch', 'delete', 'options', 'head', 'trace'];
                        if (in_array($method, $validMethods) && !isset($operation['responses'])) {
                            $errors[] = "Missing responses for {$method} {$path}";
                        }
                    }
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    protected function createBackup()
    {
        $this->displayInfo('Creating backup of current specification...');
        
        $backupDir = storage_path('api-docs/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupFile = "{$backupDir}/openapi_backup_{$timestamp}.json";

        try {
            $currentSpec = $this->generator->generate();
            $json = json_encode($currentSpec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            File::put($backupFile, $json);
            
            $this->displaySuccess("Backup created: {$backupFile}");
        } catch (\Exception $e) {
            $this->displayWarning("Failed to create backup: {$e->getMessage()}");
        }
    }

    protected function importSpecification($spec)
    {
        $this->displayInfo('Importing specification...');
        
        // Here you would implement the actual import logic
        // This might involve updating your generator's internal state
        // or saving to a configuration file/database
        
        $outputPath = storage_path('api-docs');
        if (!File::exists($outputPath)) {
            File::makeDirectory($outputPath, 0755, true);
        }

        // Save as JSON
        $jsonFile = $outputPath . '/imported_openapi.json';
        $json = json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        File::put($jsonFile, $json);

        // Save as YAML
        $yamlFile = $outputPath . '/imported_openapi.yaml';
        $yaml = Yaml::dump($spec, 4, 2, Yaml::DUMP_OBJECT_AS_MAP);
        File::put($yamlFile, $yaml);

        $this->displaySuccess("Imported specification saved to:");
        $this->line("  📄 {$jsonFile}");
        $this->line("  📄 {$yamlFile}");
    }

    protected function mergeSpecification($newSpec)
    {
        $this->displayInfo('Merging with existing specification...');
        
        try {
            $currentSpec = $this->generator->generate();
            
            // Merge paths
            if (isset($newSpec['paths'])) {
                if (!isset($currentSpec['paths'])) {
                    $currentSpec['paths'] = [];
                }
                
                foreach ($newSpec['paths'] as $path => $pathItem) {
                    if (isset($currentSpec['paths'][$path])) {
                        // Merge operations for existing path
                        $currentSpec['paths'][$path] = array_merge(
                            $currentSpec['paths'][$path],
                            $pathItem
                        );
                    } else {
                        // Add new path
                        $currentSpec['paths'][$path] = $pathItem;
                    }
                }
            }

            // Merge components
            if (isset($newSpec['components'])) {
                if (!isset($currentSpec['components'])) {
                    $currentSpec['components'] = [];
                }
                $currentSpec['components'] = array_merge_recursive(
                    $currentSpec['components'],
                    $newSpec['components']
                );
            }

            // Update info if provided
            if (isset($newSpec['info'])) {
                $currentSpec['info'] = array_merge(
                    $currentSpec['info'] ?? [],
                    $newSpec['info']
                );
            }

            $this->importSpecification($currentSpec);
            $this->displaySuccess('Specifications merged successfully');
            
        } catch (\Exception $e) {
            $this->displayError("Failed to merge specifications: {$e->getMessage()}");
        }
    }

    protected function displayImportStatistics($spec)
    {
        $this->line('');
        $this->line('<fg=white;options=bold>Import Statistics:</>');
        
        $pathCount = isset($spec['paths']) ? count($spec['paths']) : 0;
        $operationCount = 0;
        $componentCount = 0;

        if (isset($spec['paths'])) {
            foreach ($spec['paths'] as $pathItem) {
                if (is_array($pathItem)) {
                    foreach ($pathItem as $method => $operation) {
                        if (in_array($method, ['get', 'post', 'put', 'patch', 'delete', 'options', 'head'])) {
                            $operationCount++;
                        }
                    }
                }
            }
        }

        if (isset($spec['components'])) {
            foreach ($spec['components'] as $componentType => $components) {
                if (is_array($components)) {
                    $componentCount += count($components);
                }
            }
        }

        $this->line("  📊 Imported paths: {$pathCount}");
        $this->line("  🔧 Imported operations: {$operationCount}");
        $this->line("  🧩 Imported components: {$componentCount}");
        $this->line("  📋 API version: " . ($spec['info']['version'] ?? 'Unknown'));
        $this->line("  📝 API title: " . ($spec['info']['title'] ?? 'Unknown'));
    }
}
