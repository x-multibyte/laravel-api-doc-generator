<?php

namespace LaravelApiDocs\Console\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use LaravelApiDocs\StaticGenerator;

class GenerateStaticCommand extends BaseCommand
{
    protected $signature = 'api-docs:static 
                            {--output= : Output directory path}
                            {--themes=* : Specific themes to generate}
                            {--base-url= : Base URL for static files}
                            {--minify : Minify HTML output}
                            {--no-assets : Skip copying assets}
                            {--force : Overwrite existing files}';

    protected $description = 'Generate static HTML documentation files';

    protected StaticGenerator $staticGenerator;

    public function __construct($generator, StaticGenerator $staticGenerator)
    {
        parent::__construct($generator);
        $this->staticGenerator = $staticGenerator;
    }

    public function handle()
    {
        $this->displayHeader('Static Documentation Generator');

        $outputPath = $this->option('output') ?: config('api-docs.static.output_path');
        $themes = $this->option('themes') ?: config('api-docs.static.themes');
        $baseUrl = $this->option('base-url') ?: config('api-docs.static.base_url');
        $minify = $this->option('minify') || config('api-docs.static.minify_html');
        $includeAssets = !$this->option('no-assets') && config('api-docs.static.include_assets');
        $force = $this->option('force');

        // Validate themes
        $availableThemes = array_keys(config('api-docs.available_themes'));
        $themes = array_intersect($themes, $availableThemes);
        
        if (empty($themes)) {
            $this->displayError('No valid themes specified');
            return 1;
        }

        // Create output directory
        if (!File::exists($outputPath)) {
            File::makeDirectory($outputPath, 0755, true);
            $this->displayInfo("Created output directory: {$outputPath}");
        } elseif (!$force && $this->hasExistingFiles($outputPath)) {
            if (!$this->confirm('Output directory contains files. Overwrite?')) {
                $this->displayWarning('Generation cancelled');
                return 0;
            }
        }

        // Generate API specification first
        $this->displayInfo('Generating API specification...');
        $spec = $this->generator->generate();
        $this->displaySuccess('API specification generated');

        // Set up static generator
        $this->staticGenerator->configure([
            'output_path' => $outputPath,
            'base_url' => $baseUrl,
            'minify' => $minify,
            'include_assets' => $includeAssets,
            'spec' => $spec
        ]);

        $generatedFiles = [];
        $progressBar = $this->createProgressBar(count($themes) + 3);
        $progressBar->start();

        try {
            // Generate specification files
            $progressBar->setMessage('Generating specification files...');
            $specFiles = $this->staticGenerator->generateSpecificationFiles();
            $generatedFiles = array_merge($generatedFiles, $specFiles);
            $progressBar->advance();

            // Generate theme files
            foreach ($themes as $theme) {
                $progressBar->setMessage("Generating {$theme} theme...");
                $themeFiles = $this->staticGenerator->generateTheme($theme);
                $generatedFiles = array_merge($generatedFiles, $themeFiles);
                $progressBar->advance();
            }

            // Generate index page
            $progressBar->setMessage('Generating index page...');
            $indexFile = $this->staticGenerator->generateIndexPage($themes);
            $generatedFiles[] = $indexFile;
            $progressBar->advance();

            // Copy assets
            if ($includeAssets) {
                $progressBar->setMessage('Copying assets...');
                $assetFiles = $this->staticGenerator->copyAssets();
                $generatedFiles = array_merge($generatedFiles, $assetFiles);
            }
            $progressBar->advance();

            $progressBar->finish();
            $this->line('');

            // Generate sitemap if configured
            if (config('api-docs.static.generate_sitemap')) {
                $this->displayInfo('Generating sitemap...');
                $sitemapFile = $this->staticGenerator->generateSitemap($themes);
                $generatedFiles[] = $sitemapFile;
            }

            // Display results
            $this->displayResults($generatedFiles, $outputPath, $baseUrl);

        } catch (\Exception $e) {
            $progressBar->finish();
            $this->line('');
            $this->displayError("Generation failed: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }

    protected function hasExistingFiles(string $path): bool
    {
        return File::exists($path) && count(File::allFiles($path)) > 0;
    }

    protected function displayResults(array $files, string $outputPath, string $baseUrl)
    {
        $this->line('');
        $this->displaySuccess('Static documentation generated successfully!');
        $this->line('');
        
        $this->line('<fg=white;options=bold>Generated Files:</>');
        foreach ($files as $file) {
            $this->line("  📄 " . str_replace($outputPath . '/', '', $file));
        }
        
        $this->line('');
        $this->line('<fg=white;options=bold>Summary:</>');
        $this->line("  📁 Output Directory: {$outputPath}");
        $this->line("  🌐 Base URL: {$baseUrl}");
        $this->line("  📊 Total Files: " . count($files));
        $this->line("  💾 Total Size: " . $this->formatTotalSize($files));
        
        $this->line('');
        $this->line('<fg=white;options=bold>Next Steps:</>');
        $this->line('  1. Upload files to your static hosting provider');
        $this->line('  2. Configure web server to serve from: ' . $outputPath);
        $this->line("  3. Visit: {$baseUrl}/index.html");
        
        if (config('api-docs.static.generate_sitemap')) {
            $this->line("  4. Submit sitemap: {$baseUrl}/sitemap.xml");
        }
    }

    protected function formatTotalSize(array $files): string
    {
        $totalSize = 0;
        foreach ($files as $file) {
            if (File::exists($file)) {
                $totalSize += File::size($file);
            }
        }
        
        return $this->formatBytes($totalSize);
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
