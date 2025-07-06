<?php

namespace LaravelApiDocs\Tests\Factories;

class OpenApiSpecFactory extends BaseFactory
{
    protected array $states = [
        'minimal' => [
            'paths' => [],
            'components' => []
        ],
        'complex' => [
            'externalDocs' => [
                'description' => 'Find more info here',
                'url' => 'https://example.com/docs'
            ]
        ],
        'with_security' => [
            'security' => [
                ['bearerAuth' => []],
                ['apiKey' => []]
            ]
        ],
        'invalid' => [
            'openapi' => null,
            'info' => []
        ]
    ];

    protected function getDefaults(): array
    {
        $faker = $this->faker();
        
        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => $faker->words(3, true) . ' API',
                'description' => $faker->sentence(10),
                'version' => $faker->semver(),
                'contact' => [
                    'name' => $faker->name(),
                    'email' => $faker->email(),
                    'url' => $faker->url()
                ],
                'license' => [
                    'name' => $this->randomChoice(['MIT', 'Apache 2.0', 'GPL-3.0', 'BSD-3-Clause']),
                    'url' => $faker->url()
                ]
            ],
            'servers' => [
                [
                    'url' => $faker->url(),
                    'description' => 'Production server'
                ],
                [
                    'url' => 'http://localhost:8000',
                    'description' => 'Development server'
                ]
            ],
            'paths' => $this->generatePaths(),
            'components' => $this->generateComponents(),
            'tags' => $this->generateTags()
        ];
    }

    public function make(): array
    {
        return $this->attributes;
    }

    public function withPaths(int $count = 5): static
    {
        $this->attributes['paths'] = $this->generatePaths($count);
        return $this;
    }

    public function withComponents(array $components = []): static
    {
        $this->attributes['components'] = array_merge(
            $this->generateComponents(),
            $components
        );
        return $this;
    }

    public function withTags(array $tags = []): static
    {
        $this->attributes['tags'] = array_merge(
            $this->generateTags(),
            $tags
        );
        return $this;
    }

    protected function generatePaths(int $count = 5): array
    {
        $paths = [];
        $faker = $this->faker();
        
        $resources = ['users', 'posts', 'comments', 'orders', 'products', 'categories'];
        
        for ($i = 0; $i < $count; $i++) {
            $resource = $this->randomChoice($resources);
            $pathKey = "/api/{$resource}";
            
            $paths[$pathKey] = PathItemFactory::new()
                ->withResource($resource)
                ->make();
                
            // Add individual resource path
            $pathKey = "/api/{$resource}/{id}";
            $paths[$pathKey] = PathItemFactory::new()
                ->withResource($resource)
                ->withParameter('id')
                ->make();
        }
        
        return $paths;
    }

    protected function generateComponents(): array
    {
        return [
            'schemas' => SchemaFactory::new()->count(5),
            'responses' => ResponseFactory::new()->count(3),
            'parameters' => ParameterFactory::new()->count(3),
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT'
                ],
                'apiKey' => [
                    'type' => 'apiKey',
                    'in' => 'header',
                    'name' => 'X-API-Key'
                ]
            ]
        ];
    }

    protected function generateTags(): array
    {
        $faker = $this->faker();
        $tags = [];
        
        $tagNames = ['Users', 'Posts', 'Comments', 'Orders', 'Products', 'Admin'];
        
        foreach ($tagNames as $name) {
            $tags[] = [
                'name' => $name,
                'description' => $faker->sentence(),
                'externalDocs' => [
                    'description' => "Find more info about {$name}",
                    'url' => $faker->url()
                ]
            ];
        }
        
        return $tags;
    }
}
