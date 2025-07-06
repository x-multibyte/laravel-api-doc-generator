<?php

namespace LaravelApiDocs\Tests\Factories;

use Illuminate\Routing\Route;
use Illuminate\Http\Request;

class RouteFactory extends BaseFactory
{
    protected array $states = [
        'api' => [
            'prefix' => 'api'
        ],
        'web' => [
            'prefix' => 'web'
        ],
        'admin' => [
            'prefix' => 'admin',
            'middleware' => ['auth', 'admin']
        ],
        'authenticated' => [
            'middleware' => ['auth']
        ],
        'guest' => [
            'middleware' => ['guest']
        ],
        'resource' => [], // Handled in make() method
        'crud' => [], // Handled in make() method
    ];

    protected function getDefaults(): array
    {
        $faker = $this->faker();
        
        return [
            'uri' => 'api/' . $faker->word(),
            'methods' => ['GET'],
            'action' => 'TestController@index',
            'middleware' => [],
            'name' => null,
            'parameters' => []
        ];
    }

    public function make(): array
    {
        // This returns route data, not an actual Route object
        // for testing purposes
        return $this->attributes;
    }

    public function createRoute(): Route
    {
        $route = new Route(
            $this->attributes['methods'],
            $this->attributes['uri'],
            $this->attributes['action']
        );
        
        if (!empty($this->attributes['middleware'])) {
            $route->middleware($this->attributes['middleware']);
        }
        
        if ($this->attributes['name']) {
            $route->name($this->attributes['name']);
        }
        
        return $route;
    }

    public function api(): static
    {
        return $this->state('api');
    }

    public function web(): static
    {
        return $this->state('web');
    }

    public function admin(): static
    {
        return $this->state('admin');
    }

    public function authenticated(): static
    {
        return $this->state('authenticated');
    }

    public function guest(): static
    {
        return $this->state('guest');
    }

    public function withMethods(array $methods): static
    {
        $this->attributes['methods'] = array_map('strtoupper', $methods);
        return $this;
    }

    public function withUri(string $uri): static
    {
        $this->attributes['uri'] = $uri;
        return $this;
    }

    public function withAction(string $action): static
    {
        $this->attributes['action'] = $action;
        return $this;
    }

    public function withMiddleware(array $middleware): static
    {
        $this->attributes['middleware'] = $middleware;
        return $this;
    }

    public function withName(string $name): static
    {
        $this->attributes['name'] = $name;
        return $this;
    }

    public function withParameters(array $parameters): static
    {
        $this->attributes['parameters'] = $parameters;
        return $this;
    }

    public function resource(string $resource): static
    {
        $this->attributes['uri'] = "api/{$resource}";
        $this->attributes['action'] = ucfirst($resource) . 'Controller@index';
        return $this->state('resource');
    }

    public function crud(string $resource): static
    {
        return $this->state('crud')->resource($resource);
    }

    public static function createCrudRoutes(string $resource): array
    {
        $routes = [];
        $controller = ucfirst($resource) . 'Controller';
        
        $routes[] = static::new()
            ->withUri("api/{$resource}")
            ->withMethods(['GET'])
            ->withAction("{$controller}@index")
            ->withName("{$resource}.index")
            ->make();
            
        $routes[] = static::new()
            ->withUri("api/{$resource}")
            ->withMethods(['POST'])
            ->withAction("{$controller}@store")
            ->withName("{$resource}.store")
            ->make();
            
        $routes[] = static::new()
            ->withUri("api/{$resource}/{id}")
            ->withMethods(['GET'])
            ->withAction("{$controller}@show")
            ->withName("{$resource}.show")
            ->withParameters(['id'])
            ->make();
            
        $routes[] = static::new()
            ->withUri("api/{$resource}/{id}")
            ->withMethods(['PUT'])
            ->withAction("{$controller}@update")
            ->withName("{$resource}.update")
            ->withParameters(['id'])
            ->make();
            
        $routes[] = static::new()
            ->withUri("api/{$resource}/{id}")
            ->withMethods(['DELETE'])
            ->withAction("{$controller}@destroy")
            ->withName("{$resource}.destroy")
            ->withParameters(['id'])
            ->make();
            
        return $routes;
    }
}
