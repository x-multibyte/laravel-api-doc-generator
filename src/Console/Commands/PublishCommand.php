<?php

namespace LaravelApiDocs\Console\Commands;

use Illuminate\Support\Facades\Artisan;

class PublishCommand extends BaseCommand
{
    protected $signature = 'api-docs:publish 
                            {--config : Publish configuration file}
                            {--views : Publish view files}
                            {--assets : Publish asset files}
                            {--all : Publish all files}
                            {--force : Overwrite existing files}';

    protected $description = 'Publish API documentation package files';

    public function handle()
    {
        $this->displayHeader('API Documentation Publisher');

        $publishConfig = $this->option('config');
        $publishViews = $this->option('views');
        $publishAssets = $this->option('assets');
        $publishAll = $this->option('all');
        $force = $this->option('force');

        if (!$publishConfig && !$publishViews && !$publishAssets && !$publishAll) {
            $this->displayError('Please specify what to publish: --config, --views, --assets, or --all');
            return 1;
        }

        $published = [];

        // Publish configuration
        if ($publishConfig || $publishAll) {
            $this->displayInfo('Publishing configuration file...');
            $result = Artisan::call('vendor:publish', [
                '--provider' => 'LaravelApiDocs\ApiDocsServiceProvider',
                '--tag' => 'config',
                '--force' => $force
            ]);

            if ($result === 0) {
                $this->displaySuccess('Configuration file published');
                $published[] = 'config/api-docs.php';
            } else {
                $this->displayError('Failed to publish configuration file');
            }
        }

        // Publish views
        if ($publishViews || $publishAll) {
            $this->displayInfo('Publishing view files...');
            $result = Artisan::call('vendor:publish', [
                '--provider' => 'LaravelApiDocs\ApiDocsServiceProvider',
                '--tag' => 'views',
                '--force' => $force
            ]);

            if ($result === 0) {
                $this->displaySuccess('View files published');
                $published[] = 'resources/views/vendor/api-docs/';
            } else {
                $this->displayError('Failed to publish view files');
            }
        }

        // Publish assets
        if ($publishAssets || $publishAll) {
            $this->displayInfo('Publishing asset files...');
            $result = Artisan::call('vendor:publish', [
                '--provider' => 'LaravelApiDocs\ApiDocsServiceProvider',
                '--tag' => 'assets',
                '--force' => $force
            ]);

            if ($result === 0) {
                $this->displaySuccess('Asset files published');
                $published[] = 'public/vendor/api-docs/';
            } else {
                $this->displayError('Failed to publish asset files');
            }
        }

        // Display results
        if (!empty($published)) {
            $this->line('');
            $this->displaySuccess('Files published successfully!');
            $this->line('');
            $this->line('<fg=white;options=bold>Published files:</>');
            foreach ($published as $file) {
                $this->line("  📄 {$file}");
            }

            $this->line('');
            $this->line('<fg=white;options=bold>Next steps:</>');
            $this->line('  1. Review and customize the configuration in config/api-docs.php');
            $this->line('  2. Customize views in resources/views/vendor/api-docs/ if needed');
            $this->line('  3. Run "php artisan api-docs:generate" to create documentation');
            $this->line('  4. Visit /api-docs to view your API documentation');
        }

        return 0;
    }
}
