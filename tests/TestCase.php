<?php

namespace XMultibyte\ApiDoc\Tests;

use XMultibyte\ApiDoc\ApiDocsServiceProvider;
use XMultibyte\ApiDoc\Tests\Concerns\UsesFactories;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase, UsesFactories;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->setUpRoutes();
    }

    protected function getPackageProviders($app)
    {
        return [
            ApiDocsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('api-docs.title', 'Test API');
        config()->set('api-docs.version', '1.0.0');
        config()->set('api-docs.description', 'Test API Description');
        config()->set('api-docs.route_prefix', 'api-docs');
        config()->set('api-docs.middleware', ['web']);
        config()->set('api-docs.scan_routes.prefix', 'api');
        config()->set('api-docs.scan_routes.exclude', ['telescope', 'horizon']);
    }

    protected function setUpDatabase()
    {
        // Set up any required database tables for testing
    }

    protected function setUpRoutes()
    {
        // Set up test routes using factories
        $routes = array_merge(
            $this->createCrudRoutes('users'),
            $this->createCrudRoutes('posts'),
            [
                $this->createAuthenticatedRoute('api/admin/users'),
                $this->createAuthenticatedRoute('api/admin/settings', ['POST'])
            ]
        );

        foreach ($routes as $routeData) {
            $this->app['router']->addRoute(
                $routeData['methods'],
                $routeData['uri'],
                $routeData['action']
            );
        }

        // Non-API routes (should be excluded)
        $this->app['router']->get('web/home', 'WebController@home');
        $this->app['router']->get('telescope/requests', 'TelescopeController@requests');
    }

    protected function createTempFile($content, $extension = 'json')
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'api_docs_test_') . '.' . $extension;
        file_put_contents($tempFile, $content);
        return $tempFile;
    }

    protected function deleteTempFile($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
