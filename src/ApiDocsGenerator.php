<?php

namespace XMultibyte\ApiDoc;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Yaml\Yaml;

class ApiDocsGenerator
{
    protected $config;
    protected $openApiSpec;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->initializeOpenApiSpec();
    }

    protected function initializeOpenApiSpec()
    {
        $this->openApiSpec = [
            'openapi' => $this->config['openapi']['version'],
            'info' => [
                'title' => $this->config['title'],
                'description' => $this->config['description'],
                'version' => $this->config['version'],
            ],
            'servers' => $this->config['openapi']['servers'],
            'components' => [
                'securitySchemes' => $this->config['openapi']['security'],
            ],
            'paths' => [],
        ];
    }

    public function generate()
    {
        $routes = $this->getApiRoutes();
        
        foreach ($routes as $route) {
            $this->processRoute($route);
        }

        return $this->openApiSpec;
    }

    protected function getApiRoutes()
    {
        $routes = collect(Route::getRoutes())->filter(function ($route) {
            $prefix = $this->config['scan_routes']['prefix'];
            $exclude = $this->config['scan_routes']['exclude'];
            
            $uri = $route->uri();
            
            // Check if route starts with API prefix
            if (!Str::startsWith($uri, $prefix)) {
                return false;
            }
            
            // Check if route should be excluded
            foreach ($exclude as $excludePattern) {
                if (Str::contains($uri, $excludePattern)) {
                    return false;
                }
            }
            
            return true;
        });

        return $routes;
    }

    protected function processRoute($route)
    {
        $uri = '/' . ltrim($route->uri(), '/');
        $methods = $route->methods();
        
        // Convert Laravel route parameters to OpenAPI format
        $uri = preg_replace('/\{([^}]+)\}/', '{$1}', $uri);
        
        if (!isset($this->openApiSpec['paths'][$uri])) {
            $this->openApiSpec['paths'][$uri] = [];
        }
        
        foreach ($methods as $method) {
            if (in_array(strtoupper($method), ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
                $this->openApiSpec['paths'][$uri][strtolower($method)] = $this->generatePathItem($route, $method);
            }
        }
    }

    protected function generatePathItem($route, $method)
    {
        $action = $route->getAction();
        $controller = $action['controller'] ?? null;
        
        $pathItem = [
            'summary' => $this->generateSummary($route, $method),
            'description' => $this->generateDescription($route, $method),
            'tags' => $this->generateTags($route),
            'parameters' => $this->generateParameters($route),
            'responses' => $this->generateResponses($method),
        ];

        // Add request body for POST, PUT, PATCH methods
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $pathItem['requestBody'] = $this->generateRequestBody($route);
        }

        // Add security if needed
        if ($this->requiresAuthentication($route)) {
            $pathItem['security'] = [
                ['bearerAuth' => []],
            ];
        }

        return $pathItem;
    }

    protected function generateSummary($route, $method)
    {
        $action = $route->getAction();
        $controller = $action['controller'] ?? null;
        
        if ($controller) {
            [$class, $methodName] = explode('@', $controller);
            $className = class_basename($class);
            return ucfirst(strtolower($method)) . ' ' . str_replace('Controller', '', $className);
        }
        
        return ucfirst(strtolower($method)) . ' ' . $route->uri();
    }

    protected function generateDescription($route, $method)
    {
        return "Generated description for {$method} {$route->uri()}";
    }

    protected function generateTags($route)
    {
        $action = $route->getAction();
        $controller = $action['controller'] ?? null;
        
        if ($controller) {
            [$class] = explode('@', $controller);
            $className = class_basename($class);
            return [str_replace('Controller', '', $className)];
        }
        
        return ['API'];
    }

    protected function generateParameters($route)
    {
        $parameters = [];
        
        // Extract route parameters
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

    protected function generateRequestBody($route)
    {
        return [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'example' => [
                                'type' => 'string',
                                'example' => 'value',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function generateResponses($method)
    {
        $responses = [
            '200' => [
                'description' => 'Successful response',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'success' => [
                                    'type' => 'boolean',
                                    'example' => true,
                                ],
                                'data' => [
                                    'type' => 'object',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if (in_array(strtoupper($method), ['POST'])) {
            $responses['201'] = [
                'description' => 'Resource created successfully',
            ];
        }

        $responses['400'] = [
            'description' => 'Bad request',
        ];

        $responses['401'] = [
            'description' => 'Unauthorized',
        ];

        $responses['404'] = [
            'description' => 'Resource not found',
        ];

        $responses['500'] = [
            'description' => 'Internal server error',
        ];

        return $responses;
    }

    protected function requiresAuthentication($route)
    {
        $middleware = $route->middleware();
        return in_array('auth', $middleware) || in_array('auth:api', $middleware);
    }

    public function exportToJson()
    {
        return json_encode($this->generate(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function exportToYaml()
    {
        return Yaml::dump($this->generate(), 4, 2, Yaml::DUMP_OBJECT_AS_MAP);
    }

    public function importFromJson($json)
    {
        $data = json_decode($json, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $this->openApiSpec = $data;
            return true;
        }
        return false;
    }

    public function importFromYaml($yaml)
    {
        try {
            $data = Yaml::parse($yaml);
            $this->openApiSpec = $data;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
