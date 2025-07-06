<?php

namespace XMultibyte\ApiDoc;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use XMultibyte\ApiDoc\Console\Commands\CleanDocsCommand;
use XMultibyte\ApiDoc\Console\Commands\GenerateDocsCommand;
use XMultibyte\ApiDoc\Console\Commands\GenerateStaticCommand;
use XMultibyte\ApiDoc\Console\Commands\HelpCommand;
use XMultibyte\ApiDoc\Console\Commands\ImportDocsCommand;
use XMultibyte\ApiDoc\Console\Commands\PublishCommand;
use XMultibyte\ApiDoc\Console\Commands\StatusCommand;

class ApiDocsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/api-docs.php',
            'api-docs'
        );

        $this->app->singleton('api-docs', function ($app) {
            return new ApiDocsGenerator($app);
        });

        $this->app->singleton('api-docs.static', function ($app) {
            return new StaticGenerator($app);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'api-docs');

        $this->publishes([
            __DIR__.'/../config/api-docs.php' => config_path('api-docs.php'),
        ], 'api-docs-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/api-docs'),
        ], 'api-docs-views');

        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/api-docs'),
        ], 'api-docs-assets');

        $this->publishes([
            __DIR__.'/../config/api-docs.php' => config_path('api-docs.php'),
            __DIR__.'/../resources/views' => resource_path('views/vendor/api-docs'),
            __DIR__.'/../resources/assets' => public_path('vendor/api-docs'),
        ], 'api-docs');

        $this->registerRoutes();
        $this->registerCommands();
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        if (! $this->app->routesAreCached()) {
            Route::group([
                'prefix' => config('api-docs.route_prefix', 'api-docs'),
                'middleware' => config('api-docs.middleware', ['web']),
                'namespace' => 'XMultibyte\ApiDoc\Http\Controllers',
            ], function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });
        }
    }

    /**
     * Register the package commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanDocsCommand::class,
                GenerateDocsCommand::class,
                GenerateStaticCommand::class,
                HelpCommand::class,
                ImportDocsCommand::class,
                PublishCommand::class,
                StatusCommand::class,
            ]);
        }
    }
}
