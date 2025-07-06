<?php

namespace LaravelApiDocs\Tests\Factories;

class ResponseFactory extends BaseFactory
{
    protected string $statusCode = '200';

    protected array $states = [
        'success' => [
            'description' => 'Successful response'
        ],
        'created' => [
            'description' => 'Resource created successfully'
        ],
        'bad_request' => [
            'description' => 'Bad request'
        ],
        'unauthorized' => [
            'description' => 'Unauthorized'
        ],
        'not_found' => [
            'description' => 'Resource not found'
        ],
        'server_error' => [
            'description' => 'Internal server error'
        ],
        'with_headers' => [], // Handled in make() method
        'empty' => [
            'content' => null
        ]
    ];

    protected function getDefaults(): array
    {
        return [
            'description' => 'Response description',
            'content' => $this->generateContent()
        ];
    }

    public function make(): array
    {
        $response = $this->attributes;
        
        // Add headers if state is set
        if (isset($this->states['with_headers'])) {
            $response['headers'] = $this->generateHeaders();
        }
        
        return $response;
    }

    public function success(): static
    {
        $this->statusCode = '200';
        return $this->state('success');
    }

    public function created(): static
    {
        $this->statusCode = '201';
        return $this->state('created');
    }

    public function badRequest(): static
    {
        $this->statusCode = '400';
        return $this->state('bad_request');
    }

    public function unauthorized(): static
    {
        $this->statusCode = '401';
        return $this->state('unauthorized');
    }

    public function notFound(): static
    {
        $this->statusCode = '404';
        return $this->state('not_found');
    }

    public function serverError(): static
    {
        $this->statusCode = '500';
        return $this->state('server_error');
    }

    public function withHeaders(): static
    {
        return $this->state('with_headers');
    }

    public function empty(): static
    {
        return $this->state('empty');
    }

    protected function generateContent(): array
    {
        return [
            'application/json' => [
                'schema' => $this->generateResponseSchema()
            ]
        ];
    }

    protected function generateResponseSchema(): array
    {
        return match($this->statusCode) {
            '200', '201' => [
                'type' => 'object',
                'properties' => [
                    'success' => [
                        'type' => 'boolean',
                        'example' => true
                    ],
                    'data' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'example' => 1],
                            'name' => ['type' => 'string', 'example' => 'John Doe'],
                            'email' => ['type' => 'string', 'example' => 'john@example.com']
                        ]
                    ]
                ]
            ],
            '400' => [
                'type' => 'object',
                'properties' => [
                    'success' => [
                        'type' => 'boolean',
                        'example' => false
                    ],
                    'message' => [
                        'type' => 'string',
                        'example' => 'Validation failed'
                    ],
                    'errors' => [
                        'type' => 'object',
                        'additionalProperties' => [
                            'type' => 'array',
                            'items' => ['type' => 'string']
                        ]
                    ]
                ]
            ],
            default => [
                'type' => 'object',
                'properties' => [
                    'success' => [
                        'type' => 'boolean',
                        'example' => false
                    ],
                    'message' => [
                        'type' => 'string',
                        'example' => 'An error occurred'
                    ]
                ]
            ]
        };
    }

    protected function generateHeaders(): array
    {
        return [
            'X-RateLimit-Limit' => [
                'description' => 'The number of allowed requests in the current period',
                'schema' => ['type' => 'integer']
            ],
            'X-RateLimit-Remaining' => [
                'description' => 'The number of remaining requests in the current period',
                'schema' => ['type' => 'integer']
            ],
            'X-RateLimit-Reset' => [
                'description' => 'The time at which the current rate limit window resets',
                'schema' => ['type' => 'integer']
            ]
        ];
    }
}
