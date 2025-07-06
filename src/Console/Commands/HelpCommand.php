<?php

namespace XMultibyte\ApiDoc\Console\Commands;

class HelpCommand extends BaseCommand
{
    protected $signature = 'api-docs:help';

    protected $description = 'Show help information for API documentation commands';

    public function handle()
    {
        $this->displayHeader('API Documentation Help');

        $this->line('<fg=white;options=bold>Available Commands:</>');
        $this->line('');

        $commands = [
            'api-docs:generate' => [
                'description' => 'Generate API documentation from Laravel routes',
                'options' => [
                    '--format=json' => 'Output format (json, yaml, both)',
                    '--output=' => 'Output directory path',
                    '--routes=' => 'Specific routes to include (comma-separated)',
                    '--exclude=' => 'Routes to exclude (comma-separated)',
                    '--force' => 'Overwrite existing files',
                    '--minify' => 'Minify JSON output',
                    '--validate' => 'Validate generated specification'
                ]
            ],
            'api-docs:import' => [
                'description' => 'Import OpenAPI specification from JSON or YAML file',
                'arguments' => [
                    'file' => 'Path to OpenAPI specification file'
                ],
                'options' => [
                    '--validate' => 'Validate specification before import',
                    '--backup' => 'Create backup of current specification',
                    '--merge' => 'Merge with existing specification'
                ]
            ],
            'api-docs:clean' => [
                'description' => 'Clean up API documentation files and cache',
                'options' => [
                    '--backups' => 'Clean backup files',
                    '--cache' => 'Clean cached documentation',
                    '--generated' => 'Clean generated files',
                    '--all' => 'Clean all documentation files',
                    '--older-than=' => 'Clean files older than specified days',
                    '--dry-run' => 'Show what would be deleted without actually deleting'
                ]
            ],
            'api-docs:status' => [
                'description' => 'Show API documentation status and statistics',
                'options' => [
                    '--detailed' => 'Show detailed information',
                    '--routes' => 'Show route analysis',
                    '--files' => 'Show file information'
                ]
            ],
            'api-docs:static' => [
                'description' => 'Generate static HTML documentation files',
                'options' => [
                    '--output=' => 'Output directory path',
                    '--themes=*' => 'Specific themes to generate',
                    '--base-url=' => 'Base URL for static files',
                    '--minify' => 'Minify HTML output',
                    '--no-assets' => 'Skip copying assets',
                    '--force' => 'Overwrite existing files'
                ]
            ],
            'api-docs:publish' => [
                'description' => 'Publish API documentation package files',
                'options' => [
                    '--config' => 'Publish configuration file',
                    '--views' => 'Publish view files',
                    '--assets' => 'Publish asset files',
                    '--all' => 'Publish all files',
                    '--force' => 'Overwrite existing files'
                ]
            ]
        ];

        foreach ($commands as $command => $details) {
            $this->line("<fg=green>{$command}</>");
            $this->line("  {$details['description']}");
            
            if (isset($details['arguments'])) {
                $this->line('  <fg=yellow>Arguments:</>');
                foreach ($details['arguments'] as $arg => $desc) {
                    $this->line("    <fg=cyan>{$arg}</> - {$desc}");
                }
            }
            
            if (isset($details['options'])) {
                $this->line('  <fg=yellow>Options:</>');
                foreach ($details['options'] as $option => $desc) {
                    $this->line("    <fg=cyan>{$option}</> - {$desc}");
                }
            }
            
            $this->line('');
        }

        $this->line('<fg=white;options=bold>Quick Start:</>');
        $this->line('  1. <fg=cyan>php artisan api-docs:publish --all</> - Publish configuration and views');
        $this->line('  2. <fg=cyan>php artisan api-docs:generate</> - Generate documentation');
        $this->line('  3. <fg=cyan>php artisan api-docs:status</> - Check status');
        $this->line('  4. Visit <fg=cyan>/api-docs</> in your browser');
        $this->line('');

        $this->line('<fg=white;options=bold>Examples:</>');
        $this->line('  <fg=cyan>php artisan api-docs:generate --format=both --validate</>');
        $this->line('  <fg=cyan>php artisan api-docs:import openapi.json --validate --backup</>');
        $this->line('  <fg=cyan>php artisan api-docs:clean --all --older-than=7</>');
        $this->line('  <fg=cyan>php artisan api-docs:static --themes=swagger,redoc --minify</>');
        $this->line('');

        $this->line('<fg=white;options=bold>Configuration:</>');
        $this->line('  Edit <fg=cyan>config/api-docs.php</> to customize settings');
        $this->line('  Customize views in <fg=cyan>resources/views/vendor/api-docs/</>');
        $this->line('');

        return 0;
    }
}
