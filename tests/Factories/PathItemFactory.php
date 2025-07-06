<?php

namespace LaravelApiDocs\Tests\Factories;

class PathItemFactory extends BaseFactory
{
    protected string $resource = 'resource';
    protected array $parameters = [];

    protected array $states = [
        'crud' => [], // Will be handled in make() method
        'readonly' => [], // Only GET methods
        'writeonly' => [], // Only POST/PUT/DELETE methods
        'with_auth' => [
            'security' => [['bearerAuth' => []]]
        ]
    ];

    protected function getDefaults(): array
    {
        return [];
    }

    public function make(): array
    {
        $methods = $this->getHttpMethods();
        $pathItem = [];
        
        foreach ($methods as $method) {
            $pathItem[$method] = OperationFactory::new()
                ->withMethod($method)
                ->withResource($this->resource)
                ->withParameters($this->parameters)
                ->make();
        }
        
        return $pathItem;
    }

    public function withResource(string $resource): static
    {
        $this->resource = $resource;
        return $this;
    }

    public function withParameter(string $name, string $type = 'string'): static
    {
        $this->parameters[] = [
            'name' => $name,
            'in' => 'path',
            'required' => true,
            'schema' => ['type' => $type],
            'description' => "The {$name} parameter"
        ];
        return $this;
    }

    public function readonly(): static
    {
        return $this->state('readonly');
    }

    public function writeonly(): static
    {
        return $this->state('writeonly');
    }

    public function withAuth(): static
    {
        return $this->state('with_auth');
    }

    protected function getHttpMethods(): array
    {
        if (in_array('readonly', array_keys($this->states))) {
            return ['get'];
        }
        
        if (in_array('writeonly', array_keys($this->states))) {
            return ['post', 'put', 'delete'];
        }
        
        // Default CRUD operations
        if (str_contains(json_encode($this->parameters), '"name":"id"')) {
            return ['get', 'put', 'delete'];
        }
        
        return ['get', 'post'];
    }
}
