<?php

namespace LaravelApiDocs\Tests\Factories;

class FactoryManager
{
    protected static array $factories = [
        'openapi' => OpenApiSpecFactory::class,
        'path' => PathItemFactory::class,
        'operation' => OperationFactory::class,
        'request_body' => RequestBodyFactory::class,
        'response' => ResponseFactory::class,
        'schema' => SchemaFactory::class,
        'parameter' => ParameterFactory::class,
        'route' => RouteFactory::class,
        'scenario' => ScenarioFactory::class,
    ];

    public static function create(string $type, array $attributes = []): BaseFactory
    {
        if (!isset(static::$factories[$type])) {
            throw new \InvalidArgumentException("Unknown factory type: {$type}");
        }

        $factoryClass = static::$factories[$type];
        return new $factoryClass($attributes);
    }

    public static function openapi(array $attributes = []): OpenApiSpecFactory
    {
        return new OpenApiSpecFactory($attributes);
    }

    public static function path(array $attributes = []): PathItemFactory
    {
        return new PathItemFactory($attributes);
    }

    public static function operation(array $attributes = []): OperationFactory
    {
        return new OperationFactory($attributes);
    }

    public static function requestBody(array $attributes = []): RequestBodyFactory
    {
        return new RequestBodyFactory($attributes);
    }

    public static function response(array $attributes = []): ResponseFactory
    {
        return new ResponseFactory($attributes);
    }

    public static function schema(array $attributes = []): SchemaFactory
    {
        return new SchemaFactory($attributes);
    }

    public static function parameter(array $attributes = []): ParameterFactory
    {
        return new ParameterFactory($attributes);
    }

    public static function route(array $attributes = []): RouteFactory
    {
        return new RouteFactory($attributes);
    }

    public static function scenario(array $attributes = []): ScenarioFactory
    {
        return new ScenarioFactory($attributes);
    }

    public static function registerFactory(string $name, string $factoryClass): void
    {
        static::$factories[$name] = $factoryClass;
    }

    public static function getAvailableFactories(): array
    {
        return array_keys(static::$factories);
    }
}
