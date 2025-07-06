<?php

namespace XMultibyte\ApiDoc;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;
use Symfony\Component\Yaml\Yaml;
use ReflectionClass;
use ReflectionMethod;

class ApiDocsGenerator
{
    protected array $config;
    protected array $openApiSpec;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'title' => 'API Documentation',
            'version' => '1.0.0',
            'description' => 'Generated API Documentation',
            'openapi' => [
                'version' => '3.0.3',
                'servers' => []
            ],
            'scan_routes' => [
                'prefix' => 'api',
                'exclude' => ['telescope', 'horizon', 'debugbar']
            ]
        ], $config);

        $this->initializeSpec();
    }

    protected function initializeSpec(): void
    {
        $this->openApiSpec = [
            'openapi' => $this->config['openapi']['version'],
            'info' => [
                'title' => $this->config['title'],
                'version' => $this->config['version'],
                'description' => $this->config['description']
            ],
            'servers' => $this->config['openapi']['servers'] ?? [],
            'paths' => [],
            'components' => [
                'schemas' => [],
                'responses' => [],
                'parameters' => [],
                'examples' => [],
                'requestBodies' => [],
                'headers' => [],
                'securitySchemes' => [],
                'links' => [],
                'callbacks' => []
            ],
            'security' => [],
            'tags' => [],
            'externalDocs' => []
        ];
    }

    public function generate(): array
    {
        $routes = $this->getApiRoutes();
        
        foreach ($routes as $route) {
            $this->processRoute($route);
        }

        $this->generateTags();
        $this->generateSecuritySchemes();
        
        return $this->openApiSpec;
    }

    protected function getApiRoutes(): Collection
    {
        $prefix = $this->config['scan_routes']['prefix'];
        $excludePatterns = $this->config['scan_routes']['exclude'];

        return collect(Route::getRoutes())->filter(function ($route) use ($prefix, $excludePatterns) {
            $uri = $route->uri();
            
            // Check if route starts with API prefix
            if (!str_starts_with($uri, $prefix)) {
                return false;
            }

            // Check exclude patterns
            foreach ($excludePatterns as $pattern) {
                if (str_contains($uri, $pattern)) {
                    return false;
                }
            }

            return true;
        });
    }

    protected function processRoute($route): void
    {
        $uri = '/' . ltrim($route->uri(), '/');
        $methods = array_diff($route->methods(), ['HEAD']);

        if (!isset($this->openApiSpec['paths'][$uri])) {
            $this->openApiSpec['paths'][$uri] = [];
        }

        foreach ($methods as $method) {
            $method = strtolower($method);
            $this->openApiSpec['paths'][$uri][$method] = $this->generateOperation($route, $method);
        }
    }

    protected function generateOperation($route, string $method): array
    {
        $action = $route->getAction();
        $controller = $action['controller'] ?? null;
        
        $operation = [
            'summary' => $this->generateSummary($route, $method),
            'description' => $this->generateDescription($route, $method),
            'operationId' => $this->generateOperationId($route, $method),
            'tags' => $this->generateOperationTags($route),
            'parameters' => $this->generateParameters($route),
            'responses' => $this->generateResponses($route, $method)
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

    protected function generateSummary($route, string $method): string
    {
        $action = $route->getAction();
        $controller = $action['controller'] ?? null;
        
        if ($controller) {
            [$controllerClass, $methodName] = explode('@', $controller);
            $controllerName = class_basename($controllerClass);
            return ucfirst($method) . ' ' . str_replace('Controller', '', $controllerName);
        }

        return ucfirst($method) . ' ' . $route->uri();
    }

    protected function generateDescription($route, string $method): string
    {
        return "Generated description for {$method} {$route->uri()}";
    }

    protected function generateOperationId($route, string $method): string
    {
        $uri = str_replace(['/', '{', '}'], ['_', '', ''], $route->uri());
        return $method . '_' . $uri;
    }

    protected function generateOperationTags($route): array
    {
        $action = $route->getAction();
        $controller = $action['controller'] ?? null;
        
        if ($controller) {
            [$controllerClass] = explode('@', $controller);
            $controllerName = class_basename($controllerClass);
            return [str_replace('Controller', '', $controllerName)];
        }

        return ['Default'];
    }

    protected function generateParameters($route): array
    {
        $parameters = [];
        
        // Extract path parameters
        preg_match_all('/\{([^}]+)\}/', $route->uri(), $matches);
        foreach ($matches[1] as $param) {
            $parameters[] = [
                'name' => $param,
                'in' => 'path',
                'required' => true,
                'schema' => [
                    'type' => 'string'
                ],
                'description' => "The {$param} parameter"
            ];
        }

        return $parameters;
    }

    protected function generateRequestBody($route, string $method): array
    {
        return [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'description' => 'Request data'
                            ]
                        ]
                    ]
                ],
                'application/x-www-form-urlencoded' => [
                    'schema' => [
                        'type' => 'object'
                    ]
                ]
            ]
        ];
    }

    protected function generateResponses($route, string $method): array
    {
        $responses = [
            '200' => [
                'description' => 'Successful response',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'data' => [
                                    'type' => 'object'
                                ],
                                'message' => [
                                    'type' => 'string'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Add method-specific responses
        switch (strtoupper($method)) {
            case 'POST':
                $responses['201'] = [
                    'description' => 'Resource created successfully',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'data' => ['type' => 'object'],
                                    'message' => ['type' => 'string']
                                ]
                            ]
                        ]
                    ]
                ];
                break;
            case 'DELETE':
                $responses['204'] = [
                    'description' => 'Resource deleted successfully'
                ];
                break;
        }

        // Add common error responses
        $responses['400'] = [
            'description' => 'Bad request',
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'error' => ['type' => 'string'],
                            'message' => ['type' => 'string']
                        ]
                    ]
                ]
            ]
        ];

        if ($this->hasAuthMiddleware($route)) {
            $responses['401'] = [
                'description' => 'Unauthorized',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'error' => ['type' => 'string'],
                                'message' => ['type' => 'string']
                            ]
                        ]
                    ]
                ]
            ];
        }

        return $responses;
    }

    protected function hasAuthMiddleware($route): bool
    {
        $middleware = $route->middleware();
        $authMiddleware = ['auth', 'auth:api', 'auth:sanctum', 'jwt.auth'];
        
        return !empty(array_intersect($middleware, $authMiddleware));
    }

    protected function generateSecurity($route): array
    {
        $middleware = $route->middleware();
        
        if (in_array('auth:sanctum', $middleware)) {
            return [['sanctum' => []]];
        } elseif (in_array('auth:api', $middleware)) {
            return [['bearerAuth' => []]];
        } elseif (in_array('jwt.auth', $middleware)) {
            return [['jwtAuth' => []]];
        }
        
        return [['bearerAuth' => []]];
    }

    protected function generateTags(): void
    {
        $tags = [];
        
        foreach ($this->openApiSpec['paths'] as $path => $pathItem) {
            foreach ($pathItem as $method => $operation) {
                if (isset($operation['tags'])) {
                    foreach ($operation['tags'] as $tag) {
                        if (!in_array($tag, array_column($tags, 'name'))) {
                            $tags[] = [
                                'name' => $tag,
                                'description' => "Operations related to {$tag}"
                            ];
                        }
                    }
                }
            }
        }
        
        $this->openApiSpec['tags'] = $tags;
    }

    protected function generateSecuritySchemes(): void
    {
        $this->openApiSpec['components']['securitySchemes'] = [
            'bearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'JWT'
            ],
            'sanctum' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'description' => 'Laravel Sanctum token authentication'
            ],
            'jwtAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'JWT',
                'description' => 'JWT token authentication'
            ]
        ];
    }

    public function exportToJson(): string
    {
        return json_encode($this->openApiSpec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function exportToYaml(): string
    {
        return Yaml::dump($this->openApiSpec, 4, 2, Yaml::DUMP_OBJECT_AS_MAP);
    }

    public function importFromJson(string $json): bool
    {
        try {
            $spec = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }
            $this->openApiSpec = $spec;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function importFromYaml(string $yaml): bool
    {
        try {
            $spec = Yaml::parse($yaml);
            $this->openApiSpec = $spec;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getSpec(): array
    {
        return $this->openApiSpec;
    }

    public function setSpec(array $spec): void
    {
        $this->openApiSpec = $spec;
    }
}
