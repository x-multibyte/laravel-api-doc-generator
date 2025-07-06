<?php

namespace XMultibyte\ApiDoc;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Yaml\Yaml;

class ApiDocsGenerator
{
    protected Application $app;
    protected Router $router;
    protected array $config;
    protected array $openApiSpec;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->router = $app['router'];
        $this->config = config('api-docs', []);
        $this->initializeOpenApiSpec();
    }

    /**
     * Initialize the OpenAPI specification structure.
     */
    protected function initializeOpenApiSpec(): void
    {
        $this->openApiSpec = [
            'openapi' => $this->config['openapi']['version'] ?? '3.0.3',
            'info' => [
                'title' => $this->config['title'] ?? 'API Documentation',
                'description' => $this->config['description'] ?? '',
                'version' => $this->config['version'] ?? '1.0.0',
                'contact' => $this->config['contact'] ?? [],
                'license' => $this->config['license'] ?? [],
            ],
            'servers' => $this->config['openapi']['servers'] ?? [
                [
                    'url' => config('app.url'),
                    'description' => 'Development server',
                ],
            ],
            'paths' => [],
            'components' => [
                'schemas' => [],
                'securitySchemes' => $this->config['openapi']['security'] ?? [],
            ],
            'tags' => [],
        ];
    }

    /**
     * Generate the complete API documentation.
     */
    public function generate(array $options = []): array
    {
        $cacheKey = 'api-docs:spec:' . md5(serialize($options));
        
        if ($this->shouldUseCache() && !($options['force'] ?? false)) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $routes = $this->getApiRoutes($options);
        $this->processRoutes($routes);
        $this->generateTags();
        
        if ($options['validate'] ?? false) {
            $this->validateSpecification();
        }

        if ($this->shouldUseCache()) {
            Cache::put($cacheKey, $this->openApiSpec, $this->getCacheTtl());
        }

        return $this->openApiSpec;
    }

    /**
     * Get API routes based on configuration and options.
     */
    protected function getApiRoutes(array $options = []): Collection
    {
        $routes = collect($this->router->getRoutes()->getRoutes());

        // Filter by prefix
        if ($prefix = $this->config['scan_routes']['prefix'] ?? null) {
            $routes = $routes->filter(function (Route $route) use ($prefix) {
                return Str::startsWith($route->uri(), $prefix);
            });
        }

        // Apply include patterns
        if (!empty($options['routes'])) {
            $patterns = explode(',', $options['routes']);
            $routes = $routes->filter(function (Route $route) use ($patterns) {
                foreach ($patterns as $pattern) {
                    if (Str::is(trim($pattern), $route->uri())) {
                        return true;
                    }
                }
                return false;
            });
        }

        // Apply exclude patterns
        $excludePatterns = array_merge(
            $this->config['scan_routes']['exclude'] ?? [],
            !empty($options['exclude']) ? explode(',', $options['exclude']) : []
        );

        if (!empty($excludePatterns)) {
            $routes = $routes->filter(function (Route $route) use ($excludePatterns) {
                foreach ($excludePatterns as $pattern) {
                    if (Str::is($pattern, $route->uri())) {
                        return false;
                    }
                }
                return true;
            });
        }

        return $routes;
    }

    /**
     * Process routes and generate OpenAPI paths.
     */
    protected function processRoutes(Collection $routes): void
    {
        foreach ($routes as $route) {
            $this->processRoute($route);
        }
    }

    /**
     * Process a single route.
     */
    protected function processRoute(Route $route): void
    {
        $uri = '/' . ltrim($route->uri(), '/');
        $methods = $route->methods();

        // Convert Laravel route parameters to OpenAPI format
        $uri = preg_replace('/\{([^}]+)\}/', '{$1}', $uri);

        if (!isset($this->openApiSpec['paths'][$uri])) {
            $this->openApiSpec['paths'][$uri] = [];
        }

        foreach ($methods as $method) {
            if (in_array(strtoupper($method), ['HEAD', 'OPTIONS'])) {
                continue;
            }

            $this->openApiSpec['paths'][$uri][strtolower($method)] = $this->generateOperation($route, $method);
        }
    }

    /**
     * Generate OpenAPI operation for a route method.
     */
    protected function generateOperation(Route $route, string $method): array
    {
        $operation = [
            'summary' => $this->generateSummary($route, $method),
            'description' => $this->generateDescription($route, $method),
            'operationId' => $this->generateOperationId($route, $method),
            'tags' => $this->generateOperationTags($route),
            'parameters' => $this->generateParameters($route),
            'responses' => $this->generateResponses($route, $method),
        ];

        // Add request body for methods that typically have one
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $operation['requestBody'] = $this->generateRequestBody($route, $method);
        }

        // Add security if route has auth middleware
        if ($this->hasAuthMiddleware($route)) {
            $operation['security'] = $this->generateSecurity($route);
        }

        return $operation;
    }

    /**
     * Generate operation summary.
     */
    protected function generateSummary(Route $route, string $method): string
    {
        $action = $route->getActionName();
        $uri = $route->uri();
        
        // Try to extract from controller method name
        if (Str::contains($action, '@')) {
            [$controller, $methodName] = explode('@', $action);
            $methodName = Str::title(Str::camel($methodName));
            return "{$methodName} " . Str::title(strtolower($method));
        }

        return Str::title(strtolower($method)) . ' ' . $uri;
    }

    /**
     * Generate operation description.
     */
    protected function generateDescription(Route $route, string $method): string
    {
        return "Endpoint for {$method} {$route->uri()}";
    }

    /**
     * Generate operation ID.
     */
    protected function generateOperationId(Route $route, string $method): string
    {
        $uri = str_replace(['/', '{', '}'], ['_', '', ''], $route->uri());
        return strtolower($method) . '_' . $uri;
    }

    /**
     * Generate operation tags.
     */
    protected function generateOperationTags(Route $route): array
    {
        $uri = $route->uri();
        $segments = explode('/', trim($uri, '/'));
        
        if (count($segments) > 0) {
            return [Str::title($segments[0])];
        }

        return ['Default'];
    }

    /**
     * Generate parameters for the route.
     */
    protected function generateParameters(Route $route): array
    {
        $parameters = [];

        // Path parameters
        preg_match_all('/\{([^}]+)\}/', $route->uri(), $matches);
        foreach ($matches[1] as $param) {
            $parameters[] = [
                'name' => $param,
                'in' => 'path',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                ],
                'description' => "The {$param} parameter",
            ];
        }

        return $parameters;
    }

    /**
     * Generate request body for the operation.
     */
    protected function generateRequestBody(Route $route, string $method): array
    {
        return [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [],
                    ],
                ],
                'application/x-www-form-urlencoded' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * Generate responses for the operation.
     */
    protected function generateResponses(Route $route, string $method): array
    {
        $responses = [
            '200' => [
                'description' => 'Successful response',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                        ],
                    ],
                ],
            ],
        ];

        // Add common error responses
        if ($this->hasAuthMiddleware($route)) {
            $responses['401'] = [
                'description' => 'Unauthorized',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'message' => [
                                    'type' => 'string',
                                    'example' => 'Unauthenticated.',
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        $responses['422'] = [
            'description' => 'Validation error',
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                            ],
                            'errors' => [
                                'type' => 'object',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $responses;
    }

    /**
     * Generate security requirements for the operation.
     */
    protected function generateSecurity(Route $route): array
    {
        $middleware = $route->gatherMiddleware();
        
        foreach ($middleware as $mw) {
            if (Str::startsWith($mw, 'auth:')) {
                $guard = Str::after($mw, 'auth:');
                
                if ($guard === 'sanctum') {
                    return [['bearerAuth' => []]];
                }
                
                if ($guard === 'api') {
                    return [['bearerAuth' => []]];
                }
            }
            
            if ($mw === 'auth') {
                return [['bearerAuth' => []]];
            }
        }

        return [['bearerAuth' => []]];
    }

    /**
     * Check if route has authentication middleware.
     */
    protected function hasAuthMiddleware(Route $route): bool
    {
        $middleware = $route->gatherMiddleware();
        
        foreach ($middleware as $mw) {
            if (Str::startsWith($mw, 'auth') || $mw === 'auth') {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate tags from processed routes.
     */
    protected function generateTags(): void
    {
        $tags = [];
        
        foreach ($this->openApiSpec['paths'] as $path => $operations) {
            foreach ($operations as $operation) {
                if (isset($operation['tags'])) {
                    foreach ($operation['tags'] as $tag) {
                        if (!in_array($tag, array_column($tags, 'name'))) {
                            $tags[] = [
                                'name' => $tag,
                                'description' => "Operations related to {$tag}",
                            ];
                        }
                    }
                }
            }
        }

        $this->openApiSpec['tags'] = $tags;
    }

    /**
     * Validate the OpenAPI specification.
     */
    protected function validateSpecification(): bool
    {
        // Basic validation - check required fields
        $required = ['openapi', 'info', 'paths'];
        
        foreach ($required as $field) {
            if (!isset($this->openApiSpec[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        // Validate info object
        if (!isset($this->openApiSpec['info']['title'])) {
            throw new \InvalidArgumentException("Missing required field: info.title");
        }

        if (!isset($this->openApiSpec['info']['version'])) {
            throw new \InvalidArgumentException("Missing required field: info.version");
        }

        return true;
    }

    /**
     * Export specification to JSON.
     */
    public function exportToJson(bool $minify = false): string
    {
        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;
        
        if ($minify) {
            $flags = JSON_UNESCAPED_SLASHES;
        }

        return json_encode($this->openApiSpec, $flags);
    }

    /**
     * Export specification to YAML.
     */
    public function exportToYaml(): string
    {
        return Yaml::dump($this->openApiSpec, 10, 2);
    }

    /**
     * Import specification from JSON.
     */
    public function importFromJson(string $json): void
    {
        $spec = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }

        $this->openApiSpec = $spec;
    }

    /**
     * Import specification from YAML.
     */
    public function importFromYaml(string $yaml): void
    {
        try {
            $spec = Yaml::parse($yaml);
            $this->openApiSpec = $spec;
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid YAML: ' . $e->getMessage());
        }
    }

    /**
     * Save specification to file.
     */
    public function saveToFile(string $path, string $format = 'json'): void
    {
        $directory = dirname($path);
        
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $content = $format === 'yaml' ? $this->exportToYaml() : $this->exportToJson();
        File::put($path, $content);
    }

    /**
     * Load specification from file.
     */
    public function loadFromFile(string $path): void
    {
        if (!File::exists($path)) {
            throw new \InvalidArgumentException("File not found: {$path}");
        }

        $content = File::get($path);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (in_array($extension, ['yml', 'yaml'])) {
            $this->importFromYaml($content);
        } else {
            $this->importFromJson($content);
        }
    }

    /**
     * Get the current OpenAPI specification.
     */
    public function getSpecification(): array
    {
        return $this->openApiSpec;
    }

    /**
     * Set the OpenAPI specification.
     */
    public function setSpecification(array $spec): void
    {
        $this->openApiSpec = $spec;
    }

    /**
     * Check if caching should be used.
     */
    protected function shouldUseCache(): bool
    {
        return $this->config['cache']['enabled'] ?? false;
    }

    /**
     * Get cache TTL.
     */
    protected function getCacheTtl(): int
    {
        return $this->config['cache']['ttl'] ?? 3600;
    }

    /**
     * Clear the documentation cache.
     */
    public function clearCache(): void
    {
        Cache::forget('api-docs:spec:*');
    }

    /**
     * Get route statistics.
     */
    public function getRouteStats(): array
    {
        $routes = $this->getApiRoutes();
        
        return [
            'total_routes' => $routes->count(),
            'methods' => $routes->flatMap(function (Route $route) {
                return $route->methods();
            })->countBy()->toArray(),
            'middleware' => $routes->flatMap(function (Route $route) {
                return $route->gatherMiddleware();
            })->countBy()->toArray(),
        ];
    }
}
