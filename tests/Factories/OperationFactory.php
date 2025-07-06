<?php

namespace LaravelApiDocs\Tests\Factories;

class OperationFactory extends BaseFactory
{
    protected string $method = 'get';
    protected string $resource = 'resource';
    protected array $operationParameters = [];

    protected array $states = [
        'with_auth' => [
            'security' => [['bearerAuth' => []]]
        ],
        'deprecated' => [
            'deprecated' => true
        ],
        'with_examples' => [], // Handled in make() method
        'minimal' => [
            'description' => null,
            'parameters' => [],
            'requestBody' => null
        ]
    ];

    protected function getDefaults(): array
    {
        $faker = $this->faker();
        
        return [
            'summary' => $this->generateSummary(),
            'description' => $faker->paragraph(),
            'operationId' => $this->generateOperationId(),
            'tags' => [ucfirst($this->resource)],
            'parameters' => $this->operationParameters,
            'responses' => $this->generateResponses()
        ];
    }

    public function make(): array
    {
        $operation = $this->attributes;
        
        // Add request body for write operations
        if (in_array($this->method, ['post', 'put', 'patch'])) {
            $operation['requestBody'] = RequestBodyFactory::new()
                ->withResource($this->resource)
                ->make();
        }
        
        // Add examples if state is set
        if (isset($this->states['with_examples'])) {
            $operation['responses'] = $this->addExamplesToResponses($operation['responses']);
        }
        
        return $operation;
    }

    public function withMethod(string $method): static
    {
        $this->method = strtolower($method);
        $this->attributes['summary'] = $this->generateSummary();
        $this->attributes['operationId'] = $this->generateOperationId();
        return $this;
    }

    public function withResource(string $resource): static
    {
        $this->resource = $resource;
        $this->attributes['tags'] = [ucfirst($resource)];
        $this->attributes['summary'] = $this->generateSummary();
        $this->attributes['operationId'] = $this->generateOperationId();
        return $this;
    }

    public function withParameters(array $parameters): static
    {
        $this->operationParameters = $parameters;
        $this->attributes['parameters'] = $parameters;
        return $this;
    }

    public function withAuth(): static
    {
        return $this->state('with_auth');
    }

    public function deprecated(): static
    {
        return $this->state('deprecated');
    }

    public function withExamples(): static
    {
        return $this->state('with_examples');
    }

    protected function generateSummary(): string
    {
        $action = match($this->method) {
            'get' => str_contains(json_encode($this->operationParameters), '"name":"id"') ? 'Get' : 'List',
            'post' => 'Create',
            'put' => 'Update',
            'patch' => 'Partially update',
            'delete' => 'Delete',
            default => 'Process'
        };
        
        $resourceName = str_contains(json_encode($this->operationParameters), '"name":"id"') 
            ? rtrim($this->resource, 's') 
            : $this->resource;
            
        return "{$action} {$resourceName}";
    }

    protected function generateOperationId(): string
    {
        $action = match($this->method) {
            'get' => str_contains(json_encode($this->operationParameters), '"name":"id"') ? 'get' : 'list',
            'post' => 'create',
            'put' => 'update',
            'patch' => 'patch',
            'delete' => 'delete',
            default => 'process'
        };
        
        return $action . ucfirst(rtrim($this->resource, 's'));
    }

    protected function generateResponses(): array
    {
        $responses = [
            '200' => ResponseFactory::new()->success()->make()
        ];
        
        if ($this->method === 'post') {
            $responses['201'] = ResponseFactory::new()->created()->make();
        }
        
        $responses['400'] = ResponseFactory::new()->badRequest()->make();
        $responses['401'] = ResponseFactory::new()->unauthorized()->make();
        $responses['404'] = ResponseFactory::new()->notFound()->make();
        $responses['500'] = ResponseFactory::new()->serverError()->make();
        
        return $responses;
    }

    protected function addExamplesToResponses(array $responses): array
    {
        foreach ($responses as $code => &$response) {
            if (isset($response['content']['application/json']['schema'])) {
                $response['content']['application/json']['examples'] = [
                    'example1' => [
                        'summary' => 'Example response',
                        'value' => $this->generateExampleData($code)
                    ]
                ];
            }
        }
        
        return $responses;
    }

    protected function generateExampleData(string $statusCode): array
    {
        $faker = $this->faker();
        
        return match($statusCode) {
            '200', '201' => [
                'success' => true,
                'data' => [
                    'id' => $faker->numberBetween(1, 1000),
                    'name' => $faker->name(),
                    'email' => $faker->email(),
                    'created_at' => $faker->iso8601(),
                    'updated_at' => $faker->iso8601()
                ]
            ],
            '400' => [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'name' => ['The name field is required.'],
                    'email' => ['The email field must be a valid email address.']
                ]
            ],
            '401' => [
                'success' => false,
                'message' => 'Unauthorized'
            ],
            '404' => [
                'success' => false,
                'message' => 'Resource not found'
            ],
            default => [
                'success' => false,
                'message' => 'An error occurred'
            ]
        };
    }
}
