<?php

namespace LaravelApiDocs\Console\Commands;

class HelpCommand extends BaseCommand
{
    protected $signature = 'api-docs:help';
    protected $description = 'Show help for API documentation commands';

    public function handle()
    {
        $this->displayHeader('API Documentation Commands Help');

        $commands = [
            [
                'command' => 'api-docs:generate',
                'description' => 'Generate API documentation from Laravel routes',
                'options' => [
                    '--format=json|yaml|both' => 'Output format (default: json)',
                    '--output=path' => 'Output directory path',
                    '--routes=pattern' => 'Specific routes to include (comma-separated)',
                    '--exclude=pattern' => 'Routes to exclude (comma-separated)',
                    '--force' => 'Overwrite existing files',
                    '--minify' => 'Minify JSON output',
                    '--validate' => 'Validate generated specification'
                ],
                'examples' => [
                    'php artisan api-docs:generate',
                    'php artisan api-docs:generate --format=both --validate',
                    'php artisan api-docs:generate --routes="api/users/*" --exclude="api/admin/*"'
                ]
            ],
            [
                'command' => 'api-docs:import',
                'description' => 'Import OpenAPI specification from JSON or YAML file',
                'options' => [
                    '--validate' => 'Validate specification before import',
                    '--backup' => 'Create backup of current specification',
                    '--merge' => 'Merge with existing specification'
                ],
                'examples' => [
                    'php artisan api-docs:import openapi.json',
                    'php artisan api-docs:import spec.yaml --validate --backup',
                    'php artisan api-docs:import external.json --merge'
                ]
            ],
            [
                'command' => 'api-docs:clean',
                'description' => 'Clean up API documentation files and cache',
                'options' => [
                    '--backups' => 'Clean backup files',
                    '--cache' => 'Clean cached documentation',
                    '--generated' => 'Clean generated files',
                    '--all' => 'Clean all documentation files',
                    '--older-than=days' => 'Clean files older than specified days',
                    '--dry-run' => 'Show what would be deleted without actually deleting'
                ],
                'examples' => [
                    'php artisan api-docs:clean --all --dry-run',
                    'php artisan api-docs:clean --backups --older-than=30',
                    'php artisan api-docs:clean --cache --generated'
                ]
            ],
            [
                'command' => 'api-docs:status',
                'description' => 'Show API documentation status and statistics',
                'options' => [
                    '--detailed' => 'Show detailed information',
                    '--routes' => 'Show route analysis',
                    '--files' => 'Show file information'
                ],
                'examples' => [
                    'php artisan api-docs:status',
                    'php artisan api-docs:status --detailed',
                    'php artisan api-docs:status --routes --files'
                ]
            ],
            [
                'command' => 'api-docs:publish',
                'description' => 'Publish API documentation package files',
                'options' => [
                    '--config' => 'Publish configuration file',
                    '--views' => 'Publish view files',
                    '--assets' => 'Publish asset files',
                    '--all' => 'Publish all files',
                    '--force' => 'Overwrite existing files'
                ],
                'examples' => [
                    'php artisan api-docs:publish --all',
                    'php artisan api-docs:publish --config --force',
                    'php artisan api-docs:publish --views --assets'
                ]
            ]
        ];

        foreach ($commands as $commandInfo) {
            $this->displayCommandHelp($commandInfo);
        }

        $this->displayQuickStart();
    }

    protected function displayCommandHelp($commandInfo)
    {
        $this->line('<fg=yellow;options=bold>' . $commandInfo['command'] . '</>');
        $this->line('  ' . $commandInfo['description']);
        $this->line('');

        if (!empty($commandInfo['options'])) {
            $this->line('  <fg=cyan>Options:</>');
            foreach ($commandInfo['options'] as $option => $description) {
                $this->line("    <fg=green>{$option}</> - {$description}");
            }
            $this->line('');
        }

        if (!empty($commandInfo['examples'])) {
            $this->line('  <fg=cyan>Examples:</>');
            foreach ($commandInfo['examples'] as $example) {
                $this->line("    <fg=gray>{$example}</>");
            }
            $this->line('');
        }

        $this->line('');
    }

    protected function displayQuickStart()
    {
        $this->line('<fg=white;options=bold>🚀 Quick Start Guide</>');
        $this->line('');
        $this->line('  1. Publish configuration and assets:');
        $this->line('     <fg=gray>php artisan api-docs:publish --all</>');
        $this->line('');
        $this->line('  2. Generate your API documentation:');
        $this->line('     <fg=gray>php artisan api-docs:generate --validate</>');
        $this->line('');
        $this->line('  3. Check the status:');
        $this->line('     <fg=gray>php artisan api-docs:status --detailed</>');
        $this->line('');
        $this->line('  4. View your documentation:');
        $this->line('     <fg=gray>Visit /api-docs in your browser</>');
        $this->line('');
        $this->line('<fg=white;options=bold>📚 Additional Resources</>');
        $this->line('');
        $this->line('  • Configuration file: config/api-docs.php');
        $this->line('  • View templates: resources/views/vendor/api-docs/');
        $this->line('  • Generated files: storage/api-docs/');
        $this->line('  • Documentation URL: /api-docs');
        $this->line('');
    }
}
