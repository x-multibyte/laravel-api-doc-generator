<?php

namespace LaravelApiDocs\Tests\Factories;

class RequestBodyFactory extends BaseFactory
{
    protected string $resource = 'resource';

    protected array $states = [
        'required' => [
            'required' => true
        ],
        'optional' => [
            'required' => false
        ],
        'multipart' => [], // Handled in make() method
        'json_only' => [], // Handled in make() method
    ];

    protected function getDefaults(): array
    {
        return [
            'description' => "Request body for {$this->resource}",
            'required' => true,
            'content' => $this->generateContent()
        ];
    }

    public function make(): array
    {
        $requestBody = $this->attributes;
        
        // Handle multipart state
        if (isset($this->states['multipart'])) {
            $requestBody['content'] = [
                'multipart/form-data' => [
                    'schema' => SchemaFactory::new()
                        ->withResource($this->resource)
                        ->multipart()
                        ->make()
                ]
            ];
        }
        
        // Handle JSON only state
        if (isset($this->states['json_only'])) {
            $requestBody['content'] = [
                'application/json' => $requestBody['content']['application/json']
            ];
        }
        
        return $requestBody;
    }

    public function withResource(string $resource): static
    {
        $this->resource = $resource;
        $this->attributes['description'] = "Request body for {$resource}";
        $this->attributes['content'] = $this->generateContent();
        return $this;
    }

    public function required(): static
    {
        return $this->state('required');
    }

    public function optional(): static
    {
        return $this->state('optional');
    }

    public function multipart(): static
    {
        return $this->state('multipart');
    }

    public function jsonOnly(): static
    {
        return $this->state('json_only');
    }

    protected function generateContent(): array
    {
        return [
            'application/json' => [
                'schema' => SchemaFactory::new()
                    ->withResource($this->resource)
                    ->make()
            ],
            'application/x-www-form-urlencoded' => [
                'schema' => SchemaFactory::new()
                    ->withResource($this->resource)
                    ->make()
            ]
        ];
    }
}
