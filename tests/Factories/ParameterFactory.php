<?php

namespace LaravelApiDocs\Tests\Factories;

class ParameterFactory extends BaseFactory
{
    protected array $states = [
        'path' => [
            'in' => 'path',
            'required' => true
        ],
        'query' => [
            'in' => 'query',
            'required' => false
        ],
        'header' => [
            'in' => 'header',
            'required' => false
        ],
        'cookie' => [
            'in' => 'cookie',
            'required' => false
        ],
        'required' => [
            'required' => true
        ],
        'optional' => [
            'required' => false
        ],
        'pagination' => [], // Handled in make() method
        'filter' => [], // Handled in make() method
    ];

    protected function getDefaults(): array
    {
        $faker = $this->faker();
        
        return [
            'name' => $faker->word(),
            'in' => 'query',
            'description' => $faker->sentence(),
            'required' => false,
            'schema' => [
                'type' => 'string'
            ]
        ];
    }

    public function make(): array
    {
        $parameter = $this->attributes;
        
        // Handle pagination state
        if (isset($this->states['pagination'])) {
            return $this->generatePaginationParameters();
        }
        
        // Handle filter state
        if (isset($this->states['filter'])) {
            return $this->generateFilterParameters();
        }
        
        return $parameter;
    }

    public function path(string $name = null): static
    {
        if ($name) {
            $this->attributes['name'] = $name;
        }
        return $this->state('path');
    }

    public function query(string $name = null): static
    {
        if ($name) {
            $this->attributes['name'] = $name;
        }
        return $this->state('query');
    }

    public function header(string $name = null): static
    {
        if ($name) {
            $this->attributes['name'] = $name;
        }
        return $this->state('header');
    }

    public function required(): static
    {
        return $this->state('required');
    }

    public function optional(): static
    {
        return $this->state('optional');
    }

    public function pagination(): static
    {
        return $this->state('pagination');
    }

    public function filter(): static
    {
        return $this->state('filter');
    }

    public function withSchema(array $schema): static
    {
        $this->attributes['schema'] = $schema;
        return $this;
    }

    protected function generatePaginationParameters(): array
    {
        return [
            [
                'name' => 'page',
                'in' => 'query',
                'description' => 'Page number for pagination',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'default' => 1,
                    'example' => 1
                ]
            ],
            [
                'name' => 'per_page',
                'in' => 'query',
                'description' => 'Number of items per page',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 100,
                    'default' => 15,
                    'example' => 15
                ]
            ],
            [
                'name' => 'sort',
                'in' => 'query',
                'description' => 'Sort field',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                    'enum' => ['id', 'name', 'created_at', 'updated_at'],
                    'default' => 'id'
                ]
            ],
            [
                'name' => 'order',
                'in' => 'query',
                'description' => 'Sort order',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                    'enum' => ['asc', 'desc'],
                    'default' => 'asc'
                ]
            ]
        ];
    }

    protected function generateFilterParameters(): array
    {
        return [
            [
                'name' => 'search',
                'in' => 'query',
                'description' => 'Search term',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 255
                ]
            ],
            [
                'name' => 'status',
                'in' => 'query',
                'description' => 'Filter by status',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                    'enum' => ['active', 'inactive', 'pending']
                ]
            ],
            [
                'name' => 'created_from',
                'in' => 'query',
                'description' => 'Filter by creation date (from)',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                    'format' => 'date'
                ]
            ],
            [
                'name' => 'created_to',
                'in' => 'query',
                'description' => 'Filter by creation date (to)',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                    'format' => 'date'
                ]
            ]
        ];
    }
}
