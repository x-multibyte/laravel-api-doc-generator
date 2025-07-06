<?php

namespace XMultibyte\ApiDoc\Tests\Concerns;

use XMultibyte\ApiDoc\Tests\Factories\FactoryManager;

trait UsesFactories
{
    protected function factory(string $type, array $attributes = [])
    {
        return FactoryManager::create($type, $attributes);
    }

    protected function createOpenApiSpec(array $attributes = []): array
    {
        return FactoryManager::openapi($attributes)->make();
    }

    protected function createComplexApi(): array
    {
        return FactoryManager::scenario()->complexApi()->make();
    }

    protected function createSimpleApi(): array
    {
        return FactoryManager::scenario()->simpleApi()->make();
    }

    protected function createECommerceApi(): array
    {
        return FactoryManager::scenario()->eCommerce()->make();
    }

    protected function createBlogApi(): array
    {
        return FactoryManager::scenario()->blog()->make();
    }

    protected function createMinimalApi(): array
    {
        return FactoryManager::scenario()->minimal()->make();
    }

    protected function createEnterpriseApi(): array
    {
        return FactoryManager::scenario()->enterprise()->make();
    }

    protected function createUserSchema(): array
    {
        return FactoryManager::schema()->user()->make();
    }

    protected function createPostSchema(): array
    {
        return FactoryManager::schema()->post()->make();
    }

    protected function createPaginationParameters(): array
    {
        return FactoryManager::parameter()->pagination()->make();
    }

    protected function createFilterParameters(): array
    {
        return FactoryManager::parameter()->filter()->make();
    }

    protected function createCrudRoutes(string $resource): array
    {
        return FactoryManager::route()::createCrudRoutes($resource);
    }

    protected function createAuthenticatedRoute(string $uri, array $methods = ['GET']): array
    {
        return FactoryManager::route()
            ->withUri($uri)
            ->withMethods($methods)
            ->authenticated()
            ->make();
    }

    protected function createAdminRoute(string $uri, array $methods = ['GET']): array
    {
        return FactoryManager::route()
            ->withUri($uri)
            ->withMethods($methods)
            ->admin()
            ->make();
    }
}
