<?php

namespace LaravelApiDocs\Tests\Feature;

use LaravelApiDocs\Tests\TestCase;
use Illuminate\Support\Facades\File;

class StatusCommandTest extends TestCase
{
    /** @test */
    public function it_displays_basic_status()
    {
        $this->artisan('api-docs:status')
            ->expectsOutput('API Documentation Status')
            ->expectsOutput('📊 Basic Information')
            ->expectsOutputToContain('Title: Test API')
            ->expectsOutputToContain('Version: 1.0.0')
            ->expectsOutputToContain('Description: Test API Description')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_displays_configuration_status()
    {
        $this->artisan('api-docs:status')
            ->expectsOutput('⚙️  Configuration Status')
            ->expectsOutputToContain('Route Prefix: /api-docs')
            ->expectsOutputToContain('Middleware: web')
            ->expectsOutputToContain('Scan Prefix: /api')
            ->expectsOutputToContain('OpenAPI Version: 3.0.3')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_displays_route_analysis_when_requested()
    {
        $this->artisan('api-docs:status', ['--routes' => true])
            ->expectsOutput('🛣️  Route Analysis')
            ->expectsOutputToContain('Total Routes:')
            ->expectsOutputToContain('API Routes:')
            ->expectsOutputToContain('Coverage:')
            ->expectsOutputToContain('🔧 HTTP Methods:')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_displays_file_information_when_requested()
    {
        // Create some test files
        $docsDir = storage_path('api-docs');
        File::makeDirectory($docsDir, 0755, true);
        File::put($docsDir . '/openapi.json', '{}');

        $this->artisan('api-docs:status', ['--files' => true])
            ->expectsOutput('📁 File Information')
            ->expectsOutputToContain('Documentation Directory:')
            ->expectsOutputToContain('openapi.json')
            ->assertExitCode(0);

        // Clean up
        File::deleteDirectory($docsDir);
    }

    /** @test */
    public function it_displays_detailed_information()
    {
        $this->artisan('api-docs:status', ['--detailed' => true])
            ->expectsOutput('🛣️  Route Analysis')
            ->expectsOutput('📁 File Information')
            ->expectsOutput('🏥 Health Check')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_displays_health_check()
    {
        $this->artisan('api-docs:status')
            ->expectsOutput('🏥 Health Check')
            ->expectsOutput('💡 Recommendations:')
            ->expectsOutputToContain('Run "php artisan api-docs:generate"')
            ->expectsOutputToContain('Visit /api-docs')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_shows_method_distribution()
    {
        $this->artisan('api-docs:status', ['--routes' => true])
            ->expectsOutputToContain('GET:')
            ->expectsOutputToContain('POST:')
            ->expectsOutputToContain('PUT:')
            ->expectsOutputToContain('DELETE:')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_shows_controller_analysis()
    {
        $this->artisan('api-docs:status', ['--routes' => true])
            ->expectsOutput('🎮 Top Controllers:')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_handles_missing_configuration()
    {
        // Temporarily clear configuration
        config(['api-docs.title' => null]);
        config(['api-docs.version' => null]);

        $this->artisan('api-docs:status')
            ->expectsOutput('🏥 Health Check')
            ->expectsOutputToContain('❌ Issues:')
            ->expectsOutputToContain('API title not configured')
            ->expectsOutputToContain('API version not configured')
            ->assertExitCode(0);
    }
}
