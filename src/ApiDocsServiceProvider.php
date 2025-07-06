<?php

namespace LaravelApiDocs;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ApiDocsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/api-docs.php', 'api-docs'
        );

        $this->app->singleton('api-docs', function ($app) {
            return new ApiDocsGenerator($app['config']['api-docs']);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/api-docs.php' => config_path('api-docs.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/api-docs'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/api-docs'),
        ], 'assets');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'api-docs');

        $this->registerRoutes();
        $this->registerCommands();
    }

    protected function registerRoutes()
    {
        Route::group([
            'prefix' => config('api-docs.route_prefix', 'api-docs'),
            'middleware' => config('api-docs.middleware', ['web']),
            'namespace' => 'LaravelApiDocs\Http\Controllers',
        ], function () {
            Route::get('/', 'ApiDocsController@index')->name('api-docs.index');
            Route::get('/swagger', 'ApiDocsController@swagger')->name('api-docs.swagger');
            Route::get('/redoc', 'ApiDocsController@redoc')->name('api-docs.redoc');
            Route::get('/rapidoc', 'ApiDocsController@rapidoc')->name('api-docs.rapidoc');
            Route::get('/generate', 'ApiDocsController@generate')->name('api-docs.generate');
            Route::post('/import', 'ApiDocsController@import')->name('api-docs.import');
            Route::get('/export/{format}', 'ApiDocsController@export')->name('api-docs.export');
            Route::get('/spec.json', 'ApiDocsController@specJson')->name('api-docs.spec.json');
            Route::get('/spec.yaml', 'ApiDocsController@specYaml')->name('api-docs.spec.yaml');
        });
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \LaravelApiDocs\Console\Commands\GenerateDocsCommand::class,
                \LaravelApiDocs\Console\Commands\ImportDocsCommand::class,
                \LaravelApiDocs\Console\Commands\CleanDocsCommand::class,
                \LaravelApiDocs\Console\Commands\StatusCommand::class,
                \LaravelApiDocs\Console\Commands\PublishCommand::class,
                \LaravelApiDocs\Console\Commands\HelpCommand::class,
            ]);
        }
    }
}
