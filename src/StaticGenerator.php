<?php

namespace LaravelApiDocs;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class StaticGenerator
{
    protected array $config = [];
    protected array $spec = [];

    public function configure(array $config): void
    {
        $this->config = $config;
        $this->spec = $config['spec'] ?? [];
    }

    public function generateSpecificationFiles(): array
    {
        $files = [];
        $outputPath = $this->config['output_path'];

        // Generate JSON specification
        $jsonContent = json_encode($this->spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $jsonFile = $outputPath . '/openapi.json';
        File::put($jsonFile, $jsonContent);
        $files[] = $jsonFile;

        // Generate YAML specification
        $yamlContent = \Symfony\Component\Yaml\Yaml::dump($this->spec, 4, 2);
        $yamlFile = $outputPath . '/openapi.yaml';
        File::put($yamlFile, $yamlContent);
        $files[] = $yamlFile;

        return $files;
    }

    public function generateTheme(string $theme): array
    {
        $files = [];
        $outputPath = $this->config['output_path'];
        $baseUrl = $this->config['base_url'];

        // Create theme directory
        $themeDir = $outputPath . '/' . $theme;
        if (!File::exists($themeDir)) {
            File::makeDirectory($themeDir, 0755, true);
        }

        // Generate theme HTML
        $html = $this->renderThemeView($theme, $baseUrl);
        
        if ($this->config['minify']) {
            $html = $this->minifyHtml($html);
        }

        $themeFile = $themeDir . '/index.html';
        File::put($themeFile, $html);
        $files[] = $themeFile;

        // Generate standalone theme page
        $standaloneHtml = $this->renderStandaloneTheme($theme, $baseUrl);
        if ($this->config['minify']) {
            $standaloneHtml = $this->minifyHtml($standaloneHtml);
        }

        $standaloneFile = $outputPath . '/' . $theme . '.html';
        File::put($standaloneFile, $standaloneHtml);
        $files[] = $standaloneFile;

        return $files;
    }

    public function generateIndexPage(array $themes): string
    {
        $outputPath = $this->config['output_path'];
        $baseUrl = $this->config['base_url'];

        $html = $this->renderIndexView($themes, $baseUrl);
        
        if ($this->config['minify']) {
            $html = $this->minifyHtml($html);
        }

        $indexFile = $outputPath . '/index.html';
        File::put($indexFile, $html);

        return $indexFile;
    }

    public function copyAssets(): array
    {
        $files = [];
        $outputPath = $this->config['output_path'];
        $assetsDir = $outputPath . '/assets';

        if (!File::exists($assetsDir)) {
            File::makeDirectory($assetsDir, 0755, true);
        }

        // Copy CSS files
        $cssDir = $assetsDir . '/css';
        File::makeDirectory($cssDir, 0755, true);
        
        $customCss = $this->generateCustomCss();
        $cssFile = $cssDir . '/api-docs.css';
        File::put($cssFile, $customCss);
        $files[] = $cssFile;

        // Copy JavaScript files
        $jsDir = $assetsDir . '/js';
        File::makeDirectory($jsDir, 0755, true);
        
        $customJs = $this->generateCustomJs();
        $jsFile = $jsDir . '/api-docs.js';
        File::put($jsFile, $customJs);
        $files[] = $jsFile;

        // Copy images/icons
        $imgDir = $assetsDir . '/img';
        File::makeDirectory($imgDir, 0755, true);
        
        $faviconContent = $this->generateFavicon();
        $faviconFile = $imgDir . '/favicon.ico';
        File::put($faviconFile, $faviconContent);
        $files[] = $faviconFile;

        return $files;
    }

    public function generateSitemap(array $themes): string
    {
        $outputPath = $this->config['output_path'];
        $baseUrl = $this->config['base_url'];
        
        $urls = [
            $baseUrl . '/index.html'
        ];
        
        foreach ($themes as $theme) {
            $urls[] = $baseUrl . '/' . $theme . '.html';
            $urls[] = $baseUrl . '/' . $theme . '/index.html';
        }
        
        $urls[] = $baseUrl . '/openapi.json';
        $urls[] = $baseUrl . '/openapi.yaml';

        $xml = $this->generateSitemapXml($urls);
        $sitemapFile = $outputPath . '/sitemap.xml';
        File::put($sitemapFile, $xml);

        return $sitemapFile;
    }

    protected function renderThemeView(string $theme, string $baseUrl): string
    {
        $viewData = [
            'spec_url' => $baseUrl . '/openapi.json',
            'base_url' => $baseUrl,
            'theme' => $theme,
            'title' => config('api-docs.title'),
            'description' => config('api-docs.description')
        ];

        return View::make("api-docs::static.themes.{$theme}", $viewData)->render();
    }

    protected function renderStandaloneTheme(string $theme, string $baseUrl): string
    {
        $viewData = [
            'spec_url' => $baseUrl . '/openapi.json',
            'base_url' => $baseUrl,
            'theme' => $theme,
            'title' => config('api-docs.title'),
            'description' => config('api-docs.description'),
            'standalone' => true
        ];

        return View::make('api-docs::static.standalone', $viewData)->render();
    }

    protected function renderIndexView(array $themes, string $baseUrl): string
    {
        $viewData = [
            'themes' => $themes,
            'available_themes' => config('api-docs.available_themes'),
            'base_url' => $baseUrl,
            'title' => config('api-docs.title'),
            'description' => config('api-docs.description'),
            'spec' => $this->spec
        ];

        return View::make('api-docs::static.index', $viewData)->render();
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

    protected function generateCustomCss(): string
    {
        return '
/* API Documentation Static Styles */
.api-docs-static {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.api-docs-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem 0;
    margin-bottom: 2rem;
}

.api-docs-nav {
    background: #f8f9fa;
    padding: 1rem 0;
    border-bottom: 1px solid #dee2e6;
}

.api-docs-nav ul {
    list-style: none;
    display: flex;
    gap: 1rem;
    margin: 0;
    padding: 0;
}

.api-docs-nav a {
    text-decoration: none;
    color: #495057;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    transition: all 0.2s;
}

.api-docs-nav a:hover,
.api-docs-nav a.active {
    background: #007bff;
    color: white;
}

.api-docs-footer {
    background: #343a40;
    color: white;
    padding: 2rem 0;
    margin-top: 3rem;
    text-align: center;
}

@media (max-width: 768px) {
    .api-docs-nav ul {
        flex-direction: column;
    }
}
';
    }

    protected function generateCustomJs(): string
    {
        return '
// API Documentation Static JavaScript
document.addEventListener("DOMContentLoaded", function() {
    // Theme switching
    const themeSelector = document.getElementById("theme-selector");
    if (themeSelector) {
        themeSelector.addEventListener("change", function() {
            const theme = this.value;
            window.location.href = theme + ".html";
        });
    }

    // Copy to clipboard functionality
    const copyButtons = document.querySelectorAll(".copy-btn");
    copyButtons.forEach(button => {
        button.addEventListener("click", function() {
            const target = document.querySelector(this.dataset.target);
            if (target) {
                navigator.clipboard.writeText(target.textContent).then(() => {
                    this.textContent = "Copied!";
                    setTimeout(() => {
                        this.textContent = "Copy";
                    }, 2000);
                });
            }
        });
    });

    // Smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll(\'a[href^="#"]\');
    anchorLinks.forEach(link => {
        link.addEventListener("click", function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
                target.scrollIntoView({ behavior: "smooth" });
            }
        });
    });
});
';
    }

    protected function generateFavicon(): string
    {
        // Generate a simple base64 encoded favicon
        return base64_decode('AAABAAEAEBAAAAEACABoBQAAFgAAACgAAAAQAAAAIAAAAAEACAAAAAAAAAEAAAAAAAAAAAAAAAEAAAAAAAAAAAAAH1siAB9bJgAhXSQAIF4pACJdJQAjXyUAJF8nACdgJgAoYSgAK2MoACxkKQAuZyoAMGkqADBpLAAyaiIAM2wkADNsJgA1bigANm4qADpwJgA6cCoAO3EqAD1zKgBBdioAQ3YsAENcgABBdpMAS3cqAEx4KwBPfSkATn0qAFGJTgBRiU8AVJhqAFKJWABWkGsAVZJvAFeSeABaj14AXaZzAF6jdABdqHYAX6d4AGOrfgBhqnwAaKxrAGatfwB2sGcAhsF6AJDFewCdynoAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA');
    }

    protected function generateSitemapXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($url) . '</loc>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.8</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
}
