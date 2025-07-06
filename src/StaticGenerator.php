<?php

namespace XMultibyte\ApiDoc;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Symfony\Component\Yaml\Yaml;

class StaticGenerator
{
    protected array $config = [];
    protected array $spec = [];

    public function configure(array $config): void
    {
        $this->config = array_merge([
            'output_path' => storage_path('api-docs/static'),
            'base_url' => '',
            'minify' => false,
            'include_assets' => true,
            'spec' => []
        ], $config);

        $this->spec = $this->config['spec'];
    }

    public function generateSpecificationFiles(): array
    {
        $outputPath = $this->config['output_path'];
        $files = [];

        // Generate JSON specification
        $jsonFile = $outputPath . '/openapi.json';
        $json = $this->config['minify'] 
            ? json_encode($this->spec, JSON_UNESCAPED_SLASHES)
            : json_encode($this->spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        File::put($jsonFile, $json);
        $files[] = $jsonFile;

        // Generate YAML specification
        $yamlFile = $outputPath . '/openapi.yaml';
        $yaml = Yaml::dump($this->spec, 4, 2, Yaml::DUMP_OBJECT_AS_MAP);
        File::put($yamlFile, $yaml);
        $files[] = $yamlFile;

        return $files;
    }

    public function generateTheme(string $theme): array
    {
        $outputPath = $this->config['output_path'];
        $files = [];

        $viewData = [
            'spec' => $this->spec,
            'baseUrl' => $this->config['base_url'],
            'theme' => $theme
        ];

        // Generate standalone theme file
        $html = View::make("api-docs::static.themes.{$theme}", $viewData)->render();
        
        if ($this->config['minify']) {
            $html = $this->minifyHtml($html);
        }

        $themeFile = $outputPath . "/{$theme}.html";
        File::put($themeFile, $html);
        $files[] = $themeFile;

        return $files;
    }

    public function generateIndexPage(array $themes): string
    {
        $outputPath = $this->config['output_path'];
        
        $viewData = [
            'spec' => $this->spec,
            'themes' => $themes,
            'baseUrl' => $this->config['base_url'],
            'defaultTheme' => $themes[0] ?? 'swagger'
        ];

        $html = View::make('api-docs::static.index', $viewData)->render();
        
        if ($this->config['minify']) {
            $html = $this->minifyHtml($html);
        }

        $indexFile = $outputPath . '/index.html';
        File::put($indexFile, $html);

        return $indexFile;
    }

    public function copyAssets(): array
    {
        $outputPath = $this->config['output_path'];
        $assetsPath = $outputPath . '/assets';
        $files = [];

        if (!File::exists($assetsPath)) {
            File::makeDirectory($assetsPath, 0755, true);
        }

        // Copy CSS files
        $cssFiles = [
            'swagger-ui.css' => $this->getSwaggerUiCss(),
            'redoc.css' => $this->getRedocCss(),
            'rapidoc.css' => $this->getRapidocCss(),
            'custom.css' => $this->getCustomCss()
        ];

        foreach ($cssFiles as $filename => $content) {
            $file = $assetsPath . '/' . $filename;
            File::put($file, $content);
            $files[] = $file;
        }

        // Copy JS files
        $jsFiles = [
            'swagger-ui-bundle.js' => $this->getSwaggerUiJs(),
            'redoc.standalone.js' => $this->getRedocJs(),
            'rapidoc-min.js' => $this->getRapidocJs(),
            'custom.js' => $this->getCustomJs()
        ];

        foreach ($jsFiles as $filename => $content) {
            $file = $assetsPath . '/' . $filename;
            File::put($file, $content);
            $files[] = $file;
        }

        return $files;
    }

    public function generateSitemap(array $themes): string
    {
        $outputPath = $this->config['output_path'];
        $baseUrl = rtrim($this->config['base_url'], '/');
        
        $urls = [
            $baseUrl . '/index.html'
        ];

        foreach ($themes as $theme) {
            $urls[] = $baseUrl . "/{$theme}.html";
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$url}</loc>\n";
            $xml .= "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "  </url>\n";
        }
        
        $xml .= '</urlset>';

        $sitemapFile = $outputPath . '/sitemap.xml';
        File::put($sitemapFile, $xml);

        return $sitemapFile;
    }

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

    protected function getSwaggerUiCss(): string
    {
        return <<<CSS
/* Swagger UI CSS - Minified version would be loaded from CDN in production */
.swagger-ui { font-family: sans-serif; }
.swagger-ui .topbar { display: none; }
CSS;
    }

    protected function getRedocCss(): string
    {
        return <<<CSS
/* ReDoc CSS - Minified version would be loaded from CDN in production */
body { margin: 0; padding: 0; }
CSS;
    }

    protected function getRapidocCss(): string
    {
        return <<<CSS
/* RapiDoc CSS - Minified version would be loaded from CDN in production */
rapi-doc { height: 100vh; }
CSS;
    }

    protected function getCustomCss(): string
    {
        return <<<CSS
/* Custom CSS for API documentation */
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    margin: 0;
    padding: 0;
}

.api-docs-header {
    background: #1f2937;
    color: white;
    padding: 1rem;
    text-align: center;
}

.theme-selector {
    margin: 1rem;
    text-align: center;
}

.theme-selector button {
    margin: 0 0.5rem;
    padding: 0.5rem 1rem;
    border: 1px solid #ccc;
    background: white;
    cursor: pointer;
    border-radius: 4px;
}

.theme-selector button.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}
CSS;
    }

    protected function getSwaggerUiJs(): string
    {
        return <<<JS
/* Swagger UI JS - Minified version would be loaded from CDN in production */
console.log('Swagger UI loaded');
JS;
    }

    protected function getRedocJs(): string
    {
        return <<<JS
/* ReDoc JS - Minified version would be loaded from CDN in production */
console.log('ReDoc loaded');
JS;
    }

    protected function getRapidocJs(): string
    {
        return <<<JS
/* RapiDoc JS - Minified version would be loaded from CDN in production */
console.log('RapiDoc loaded');
JS;
    }

    protected function getCustomJs(): string
    {
        return <<<JS
/* Custom JavaScript for API documentation */
document.addEventListener('DOMContentLoaded', function() {
    // Theme switching functionality
    const themeButtons = document.querySelectorAll('.theme-btn');
    const themeContainers = document.querySelectorAll('.theme-container');
    
    themeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const theme = this.dataset.theme;
            
            // Update active button
            themeButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Show selected theme container
            themeContainers.forEach(container => {
                container.style.display = container.id === theme + '-container' ? 'block' : 'none';
            });
        });
    });
    
    // Initialize first theme
    if (themeButtons.length > 0) {
        themeButtons[0].click();
    }
});
JS;
    }
}
