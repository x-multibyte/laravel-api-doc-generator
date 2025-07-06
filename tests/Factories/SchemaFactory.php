<?php

namespace LaravelApiDocs\Tests\Factories;

class SchemaFactory extends BaseFactory
{
    protected string $resource = 'resource';
    protected string $schemaType = 'object';

    protected array $states = [
        'user' => [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'example' => 1],
                'name' => ['type' => 'string', 'example' => 'John Doe'],
                'email' => ['type' => 'string', 'format' => 'email', 'example' => 'john@example.com'],
                'created_at' => ['type' => 'string', 'format' => 'date-time'],
                'updated_at' => ['type' => 'string', 'format' => 'date-time']
            ],
            'required' => ['name', 'email']
        ],
        'post' => [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'example' => 1],
                'title' => ['type' => 'string', 'example' => 'Sample Post'],
                'content' => ['type' => 'string', 'example' => 'This is the post content'],
                'author_id' => ['type' => 'integer', 'example' => 1],
                'published' => ['type' => 'boolean', 'example' => true],
                'created_at' => ['type' => 'string', 'format' => 'date-time'],
                'updated_at' => ['type' => 'string', 'format' => 'date-time']
            ],
            'required' => ['title', 'content', 'author_id']
        ],
        'array' => [
            'type' => 'array'
        ],
        'string' => [
            'type' => 'string'
        ],
        'integer' => [
            'type' => 'integer'
        ],
        'boolean' => [
            'type' => 'boolean'
        ],
        'multipart' => [], // Handled in make() method
        'with_validation' => [], // Handled in make() method
    ];

    protected function getDefaults(): array
    {
        return [
            'type' => $this->schemaType,
            'properties' => $this->generateProperties(),
            'required' => $this->generateRequired()
        ];
    }

    public function make(): array
    {
        $schema = $this->attributes;
        
        // Handle multipart state
        if (isset($this->states['multipart'])) {
            $schema = $this->generateMultipartSchema();
        }
        
        // Handle validation state
        if (isset($this->states['with_validation'])) {
            $schema = $this->addValidationRules($schema);
        }
        
        return $schema;
    }

    public function withResource(string $resource): static
    {
        $this->resource = $resource;
        
        // Update properties based on resource
        if (method_exists($this, "generate{$resource}Properties")) {
            $this->attributes['properties'] = $this->{"generate{$resource}Properties"}();
        } else {
            $this->attributes['properties'] = $this->generateProperties();
        }
        
        return $this;
    }

    public function asArray(): static
    {
        $this->schemaType = 'array';
        $this->attributes = [
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'properties' => $this->generateProperties()
            ]
        ];
        return $this;
    }

    public function asString(): static
    {
        return $this->state('string');
    }

    public function asInteger(): static
    {
        return $this->state('integer');
    }

    public function asBoolean(): static
    {
        return $this->state('boolean');
    }

    public function user(): static
    {
        return $this->state('user');
    }

    public function post(): static
    {
        return $this->state('post');
    }

    public function multipart(): static
    {
        return $this->state('multipart');
    }

    public function withValidation(): static
    {
        return $this->state('with_validation');
    }

    protected function generateProperties(): array
    {
        $faker = $this->faker();
        
        return [
            'id' => [
                'type' => 'integer',
                'description' => 'Unique identifier',
                'example' => $faker->numberBetween(1, 1000)
            ],
            'name' => [
                'type' => 'string',
                'description' => 'Name of the resource',
                'example' => $faker->name()
            ],
            'description' => [
                'type' => 'string',
                'description' => 'Description of the resource',
                'example' => $faker->sentence()
            ],
            'created_at' => [
                'type' => 'string',
                'format' => 'date-time',
                'description' => 'Creation timestamp',
                'example' => $faker->iso8601()
            ],
            'updated_at' => [
                'type' => 'string',
                'format' => 'date-time',
                'description' => 'Last update timestamp',
                'example' => $faker->iso8601()
            ]
        ];
    }

    protected function generateRequired(): array
    {
        return ['name'];
    }

    protected function generateMultipartSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'file' => [
                    'type' => 'string',
                    'format' => 'binary',
                    'description' => 'File to upload'
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Name of the resource'
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Description of the resource'
                ]
            ],
            'required' => ['file', 'name']
        ];
    }

    protected function addValidationRules(array $schema): array
    {
        if (isset($schema['properties'])) {
            foreach ($schema['properties'] as $property => &$definition) {
                switch ($definition['type']) {
                    case 'string':
                        $definition['minLength'] = 1;
                        $definition['maxLength'] = 255;
                        if ($property === 'email') {
                            $definition['format'] = 'email';
                        }
                        break;
                    case 'integer':
                        $definition['minimum'] = 1;
                        $definition['maximum'] = 999999;
                        break;
                    case 'array':
                        $definition['minItems'] = 0;
                        $definition['maxItems'] = 100;
                        break;
                }
            }
        }
        
        return $schema;
    }

    protected function generateUsersProperties(): array
    {
        $faker = $this->faker();
        
        return [
            'id' => ['type' => 'integer', 'example' => $faker->numberBetween(1, 1000)],
            'name' => ['type' => 'string', 'example' => $faker->name()],
            'email' => ['type' => 'string', 'format' => 'email', 'example' => $faker->email()],
            'email_verified_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
            'created_at' => ['type' => 'string', 'format' => 'date-time'],
            'updated_at' => ['type' => 'string', 'format' => 'date-time']
        ];
    }

    protected function generatePostsProperties(): array
    {
        $faker = $this->faker();
        
        return [
            'id' => ['type' => 'integer', 'example' => $faker->numberBetween(1, 1000)],
            'title' => ['type' => 'string', 'example' => $faker->sentence(6, true)],
            'slug' => ['type' => 'string', 'example' => $faker->slug()],
            'content' => ['type' => 'string', 'example' => $faker->paragraphs(3, true)],
            'excerpt' => ['type' => 'string', 'example' => $faker->sentence()],
            'author_id' => ['type' => 'integer', 'example' => $faker->numberBetween(1, 100)],
            'published' => ['type' => 'boolean', 'example' => $faker->boolean()],
            'published_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
            'created_at' => ['type' => 'string', 'format' => 'date-time'],
            'updated_at' => ['type' => 'string', 'format' => 'date-time']
        ];
    }
}
