<?php

namespace XMultibyte\ApiDoc;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class StaticGenerator
{
    protected Application $app;
    protected ApiDocsGenerator $generator;
    protected array $config;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->generator = $app['api-docs'];
        $this->config = config('api-docs.static', []);
    }

    /**
     * Generate static documentation files.
     */
    public function generate(array $options = []): array
    {
        $outputPath = $options['output'] ?? $this->config['output_path'] ?? storage_path('api-docs/static');
        $themes = $options['themes'] ?? $this->config['themes'] ?? ['swagger', 'redoc', 'rapidoc', 'custom'];
        $baseUrl = $options['base_url'] ?? $this->config['base_url'] ?? '/api-docs-static';
        $minifyHtml = $options['minify'] ?? $this->config['minify_html'] ?? false;
        $includeAssets = $options['include_assets'] ?? $this->config['include_assets'] ?? true;
        $generateSitemap = $options['generate_sitemap'] ?? $this->config['generate_sitemap'] ?? true;

        // Ensure output directory exists
        if (!File::isDirectory($outputPath)) {
            File::makeDirectory($outputPath, 0755, true);
        }

        $generatedFiles = [];

        // Generate OpenAPI specification files
        $spec = $this->generator->generate();
        
        $jsonPath = $outputPath . '/openapi.json';
        $yamlPath = $outputPath . '/openapi.yaml';
        
        File::put($jsonPath, $this->generator->exportToJson($minifyHtml));
        File::put($yamlPath, $this->generator->exportToYaml());
        
        $generatedFiles[] = $jsonPath;
        $generatedFiles[] = $yamlPath;

        // Generate main index page
        $indexContent = $this->generateIndexPage($themes, $baseUrl);
        if ($minifyHtml) {
            $indexContent = $this->minifyHtml($indexContent);
        }
        
        $indexPath = $outputPath . '/index.html';
        File::put($indexPath, $indexContent);
        $generatedFiles[] = $indexPath;

        // Generate theme-specific pages
        foreach ($themes as $theme) {
            $themeContent = $this->generateThemePage($theme, $baseUrl);
            if ($minifyHtml) {
                $themeContent = $this->minifyHtml($themeContent);
            }
            
            $themePath = $outputPath . "/{$theme}.html";
            File::put($themePath, $themeContent);
            $generatedFiles[] = $themePath;
        }

        // Generate standalone pages
        foreach ($themes as $theme) {
            $standaloneContent = $this->generateStandalonePage($theme, $spec);
            if ($minifyHtml) {
                $standaloneContent = $this->minifyHtml($standaloneContent);
            }
            
            $standalonePath = $outputPath . "/standalone-{$theme}.html";
            File::put($standalonePath, $standaloneContent);
            $generatedFiles[] = $standalonePath;
        }

        // Copy assets if requested
        if ($includeAssets) {
            $assetsPath = $outputPath . '/assets';
            $this->copyAssets($assetsPath);
            $generatedFiles = array_merge($generatedFiles, $this->getAssetFiles($assetsPath));
        }

        // Generate sitemap if requested
        if ($generateSitemap) {
            $sitemapContent = $this->generateSitemap($themes, $baseUrl);
            $sitemapPath = $outputPath . '/sitemap.xml';
            File::put($sitemapPath, $sitemapContent);
            $generatedFiles[] = $sitemapPath;
        }

        return [
            'output_path' => $outputPath,
            'generated_files' => $generatedFiles,
            'themes' => $themes,
            'file_count' => count($generatedFiles),
            'total_size' => $this->calculateTotalSize($generatedFiles),
        ];
    }

    /**
     * Generate the main index page.
     */
    protected function generateIndexPage(array $themes, string $baseUrl): string
    {
        return View::make('api-docs::static.index', [
            'themes' => $themes,
            'baseUrl' => $baseUrl,
            'config' => $this->config,
            'title' => config('api-docs.title', 'API Documentation'),
            'description' => config('api-docs.description', ''),
        ])->render();
    }

    /**
     * Generate a theme-specific page.
     */
    protected function generateThemePage(string $theme, string $baseUrl): string
    {
        return View::make("api-docs::static.themes.{$theme}", [
            'theme' => $theme,
            'baseUrl' => $baseUrl,
            'specUrl' => $baseUrl . '/openapi.json',
            'config' => $this->config,
            'title' => config('api-docs.title', 'API Documentation'),
            'description' => config('api-docs.description', ''),
        ])->render();
    }

    /**
     * Generate a standalone page with embedded specification.
     */
    protected function generateStandalonePage(string $theme, array $spec): string
    {
        return View::make("api-docs::static.standalone", [
            'theme' => $theme,
            'spec' => json_encode($spec),
            'config' => $this->config,
            'title' => config('api-docs.title', 'API Documentation'),
            'description' => config('api-docs.description', ''),
        ])->render();
    }

    /**
     * Generate sitemap.xml.
     */
    protected function generateSitemap(array $themes, string $baseUrl): string
    {
        $urls = [
            $baseUrl . '/index.html',
        ];

        foreach ($themes as $theme) {
            $urls[] = $baseUrl . "/{$theme}.html";
            $urls[] = $baseUrl . "/standalone-{$theme}.html";
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($urls as $url) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($url) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . PHP_EOL;
            $xml .= '    <changefreq>weekly</changefreq>' . PHP_EOL;
            $xml .= '    <priority>0.8</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>' . PHP_EOL;

        return $xml;
    }

    /**
     * Copy assets to the output directory.
     */
    protected function copyAssets(string $assetsPath): void
    {
        $sourcePath = __DIR__ . '/../resources/assets';
        
        if (File::isDirectory($sourcePath)) {
            File::copyDirectory($sourcePath, $assetsPath);
        }

        // Copy published assets if they exist
        $publishedAssetsPath = public_path('vendor/api-docs');
        if (File::isDirectory($publishedAssetsPath)) {
            File::copyDirectory($publishedAssetsPath, $assetsPath);
        }
    }

    /**
     * Get list of asset files.
     */
    protected function getAssetFiles(string $assetsPath): array
    {
        if (!File::isDirectory($assetsPath)) {
            return [];
        }

        return File::allFiles($assetsPath);
    }

    /**
     * Minify HTML content.
     */
    protected function minifyHtml(string $html): string
    {
        // Remove comments
        $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);
        
        // Remove extra whitespace
        $html = preg_replace('/\s+/', ' ', $html);
        
        // Remove whitespace around tags
        $html = preg_replace('/>\s+</', '><', $html);
        
        return trim($html);
    }

    /**
     * Calculate total size of generated files.
     */
    protected function calculateTotalSize(array $files): int
    {
        $totalSize = 0;
        
        foreach ($files as $file) {
            if (File::exists($file)) {
                $totalSize += File::size($file);
            }
        }

        return $totalSize;
    }

    /**
     * Clean up old static files.
     */
    public function cleanup(array $options = []): array
    {
        $outputPath = $options['output'] ?? $this->config['output_path'] ?? storage_path('api-docs/static');
        $olderThan = $options['older_than'] ?? 7; // days
        $dryRun = $options['dry_run'] ?? false;

        if (!File::isDirectory($outputPath)) {
            return [
                'deleted_files' => [],
                'deleted_count' => 0,
                'freed_space' => 0,
            ];
        }

        $cutoffTime = now()->subDays($olderThan)->timestamp;
        $deletedFiles = [];
        $freedSpace = 0;

        $files = File::allFiles($outputPath);
        
        foreach ($files as $file) {
            if ($file->getMTime() < $cutoffTime) {
                $size = $file->getSize();
                
                if (!$dryRun) {
                    File::delete($file->getPathname());
                }
                
                $deletedFiles[] = $file->getPathname();
                $freedSpace += $size;
            }
        }

        return [
            'deleted_files' => $deletedFiles,
            'deleted_count' => count($deletedFiles),
            'freed_space' => $freedSpace,
            'dry_run' => $dryRun,
        ];
    }

    /**
     * Get static generation statistics.
     */
    public function getStats(): array
    {
        $outputPath = $this->config['output_path'] ?? storage_path('api-docs/static');
        
        if (!File::isDirectory($outputPath)) {
            return [
                'exists' => false,
                'file_count' => 0,
                'total_size' => 0,
                'last_generated' => null,
            ];
        }

        $files = File::allFiles($outputPath);
        $totalSize = 0;
        $lastModified = 0;

        foreach ($files as $file) {
            $totalSize += $file->getSize();
            $lastModified = max($lastModified, $file->getMTime());
        }

        return [
            'exists' => true,
            'file_count' => count($files),
            'total_size' => $totalSize,
            'last_generated' => $lastModified ? date('Y-m-d H:i:s', $lastModified) : null,
            'output_path' => $outputPath,
        ];
    }
}
