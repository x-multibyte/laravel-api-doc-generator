<?php

namespace XMultibyte\ApiDoc\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use XMultibyte\ApiDoc\ApiDocsGenerator;
use XMultibyte\ApiDoc\StaticGenerator;

class ApiDocsController extends Controller
{
    protected ApiDocsGenerator $generator;
    protected StaticGenerator $staticGenerator;

    public function __construct(ApiDocsGenerator $generator, StaticGenerator $staticGenerator)
    {
        $this->generator = $generator;
        $this->staticGenerator = $staticGenerator;
    }

    /**
     * Display the main documentation page.
     */
    public function index(Request $request)
    {
        $theme = $request->get('theme', config('api-docs.default_theme', 'swagger'));
        $availableThemes = config('api-docs.available_themes', []);

        if (!array_key_exists($theme, $availableThemes)) {
            $theme = config('api-docs.default_theme', 'swagger');
        }

        return View::make('api-docs::index', [
            'theme' => $theme,
            'availableThemes' => $availableThemes,
            'config' => config('api-docs'),
            'title' => config('api-docs.title', 'API Documentation'),
            'description' => config('api-docs.description', ''),
        ]);
    }

    /**
     * Display Swagger UI theme.
     */
    public function swagger()
    {
        return View::make('api-docs::themes.swagger', [
            'config' => config('api-docs'),
            'specUrl' => route('api-docs.spec.json'),
        ]);
    }

    /**
     * Display ReDoc theme.
     */
    public function redoc()
    {
        return View::make('api-docs::themes.redoc', [
            'config' => config('api-docs'),
            'specUrl' => route('api-docs.spec.json'),
        ]);
    }

    /**
     * Display RapiDoc theme.
     */
    public function rapidoc()
    {
        return View::make('api-docs::themes.rapidoc', [
            'config' => config('api-docs'),
            'specUrl' => route('api-docs.spec.json'),
        ]);
    }

    /**
     * Display custom theme.
     */
    public function custom()
    {
        return View::make('api-docs::themes.custom', [
            'config' => config('api-docs'),
            'specUrl' => route('api-docs.spec.json'),
        ]);
    }

    /**
     * Get OpenAPI specification as JSON.
     */
    public function specJson(Request $request)
    {
        $options = [
            'force' => $request->boolean('force', false),
            'validate' => $request->boolean('validate', false),
            'routes' => $request->get('routes'),
            'exclude' => $request->get('exclude'),
        ];

        $spec = $this->generator->generate($options);
        $minify = $request->boolean('minify', false);

        return response($this->generator->exportToJson($minify))
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'inline; filename="openapi.json"');
    }

    /**
     * Get OpenAPI specification as YAML.
     */
    public function specYaml(Request $request)
    {
        $options = [
            'force' => $request->boolean('force', false),
            'validate' => $request->boolean('validate', false),
            'routes' => $request->get('routes'),
            'exclude' => $request->get('exclude'),
        ];

        $spec = $this->generator->generate($options);

        return response($this->generator->exportToYaml())
            ->header('Content-Type', 'application/x-yaml')
            ->header('Content-Disposition', 'inline; filename="openapi.yaml"');
    }

    /**
     * Import OpenAPI specification.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json,yaml,yml',
            'merge' => 'boolean',
            'backup' => 'boolean',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getPathname());
        $extension = $file->getClientOriginalExtension();

        try {
            if ($request->boolean('backup', false)) {
                $this->createBackup();
            }

            if (in_array($extension, ['yml', 'yaml'])) {
                $this->generator->importFromYaml($content);
            } else {
                $this->generator->importFromJson($content);
            }

            if ($request->boolean('merge', false)) {
                // Merge logic would go here
                // For now, we just replace the specification
            }

            return response()->json([
                'success' => true,
                'message' => 'Specification imported successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import specification: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Export current specification.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'json');
        $minify = $request->boolean('minify', false);

        $spec = $this->generator->generate();

        if ($format === 'yaml') {
            $content = $this->generator->exportToYaml();
            $contentType = 'application/x-yaml';
            $filename = 'openapi.yaml';
        } else {
            $content = $this->generator->exportToJson($minify);
            $contentType = 'application/json';
            $filename = 'openapi.json';
        }

        return response($content)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Generate static documentation files.
     */
    public function generateStatic(Request $request)
    {
        $options = [
            'themes' => $request->get('themes') ? explode(',', $request->get('themes')) : null,
            'output' => $request->get('output'),
            'base_url' => $request->get('base_url'),
            'minify' => $request->boolean('minify', false),
            'include_assets' => $request->boolean('include_assets', true),
            'generate_sitemap' => $request->boolean('generate_sitemap', true),
        ];

        try {
            $result = $this->staticGenerator->generate($options);

            return response()->json([
                'success' => true,
                'message' => 'Static files generated successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate static files: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get documentation statistics.
     */
    public function stats()
    {
        $routeStats = $this->generator->getRouteStats();
        $staticStats = $this->staticGenerator->getStats();

        return response()->json([
            'routes' => $routeStats,
            'static' => $staticStats,
            'cache' => [
                'enabled' => config('api-docs.cache.enabled', false),
                'ttl' => config('api-docs.cache.ttl', 3600),
            ],
        ]);
    }

    /**
     * Clear documentation cache.
     */
    public function clearCache()
    {
        try {
            $this->generator->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a backup of the current specification.
     */
    protected function createBackup(): void
    {
        $backupPath = storage_path('api-docs/backups');
        
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "openapi_backup_{$timestamp}.json";
        
        $spec = $this->generator->generate();
        file_put_contents(
            $backupPath . '/' . $filename,
            $this->generator->exportToJson(false)
        );
    }
}
