<?php

namespace LaravelApiDocs\Tests\Feature;

use LaravelApiDocs\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class GenerateDocsCommandTest extends TestCase
{
    protected string $outputPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->outputPath = storage_path('api-docs-test');
        
        // Clean up before each test
        if (File::exists($this->outputPath)) {
            File::deleteDirectory($this->outputPath);
        }
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        if (File::exists($this->outputPath)) {
            File::deleteDirectory($this->outputPath);
        }
        parent::tearDown();
    }

    /** @test */
    public function it_generates_documentation_successfully()
    {
        $this->artisan('api-docs:generate', ['--output' => $this->outputPath])
            ->expectsOutput('API Documentation Generator')
            ->expectsOutput('Documentation generated successfully!')
            ->assertExitCode(0);

        $this->assertTrue(File::exists($this->outputPath . '/openapi.json'));
    }

    /** @test */
    public function it_generates_both_json_and_yaml_formats()
    {
        $this->artisan('api-docs:generate', [
            '--format' => 'both',
            '--output' => $this->outputPath
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($this->outputPath . '/openapi.json'));
        $this->assertTrue(File::exists($this->outputPath . '/openapi.yaml'));
    }

    /** @test */
    public function it_validates_generated_specification()
    {
        $this->artisan('api-docs:generate', [
            '--validate' => true,
            '--output' => $this->outputPath
        ])
        ->expectsOutput('Validating OpenAPI specification...')
        ->expectsOutput('Specification is valid')
        ->assertExitCode(0);
    }

    /** @test */
    public function it_creates_output_directory_if_not_exists()
    {
        $nonExistentPath = $this->outputPath . '/nested/path';
        
        $this->artisan('api-docs:generate', ['--output' => $nonExistentPath])
            ->assertExitCode(0);

        $this->assertTrue(File::exists($nonExistentPath));
        $this->assertTrue(File::exists($nonExistentPath . '/openapi.json'));
    }

    /** @test */
    public function it_overwrites_files_with_force_option()
    {
        // Create initial file
        File::makeDirectory($this->outputPath, 0755, true);
        File::put($this->outputPath . '/openapi.json', '{"old": "content"}');
        
        $this->artisan('api-docs:generate', [
            '--output' => $this->outputPath,
            '--force' => true
        ])->assertExitCode(0);

        $content = File::get($this->outputPath . '/openapi.json');
        $decoded = json_decode($content, true);
        $this->assertEquals('3.0.3', $decoded['openapi']);
    }

    /** @test */
    public function it_generates_minified_json()
    {
        $this->artisan('api-docs:generate', [
            '--output' => $this->outputPath,
            '--minify' => true
        ])->assertExitCode(0);

        $content = File::get($this->outputPath . '/openapi.json');
        
        // Minified JSON should not contain pretty formatting
        $this->assertStringNotContains("\n    ", $content);
        $this->assertStringNotContains("  ", $content);
    }

    /** @test */
    public function it_filters_routes_by_pattern()
    {
        $this->artisan('api-docs:generate', [
            '--output' => $this->outputPath,
            '--routes' => 'api/users*'
        ])->assertExitCode(0);

        $content = File::get($this->outputPath . '/openapi.json');
        $spec = json_decode($content, true);

        $this->assertArrayHasKey('/api/users', $spec['paths']);
        $this->assertArrayHasKey('/api/users/{id}', $spec['paths']);
        $this->assertArrayNotHasKey('/api/posts', $spec['paths']);
    }

    /** @test */
    public function it_excludes_routes_by_pattern()
    {
        $this->artisan('api-docs:generate', [
            '--output' => $this->outputPath,
            '--exclude' => 'api/admin*'
        ])->assertExitCode(0);

        $content = File::get($this->outputPath . '/openapi.json');
        $spec = json_decode($content, true);

        $this->assertArrayHasKey('/api/users', $spec['paths']);
        $this->assertArrayNotHasKey('/api/admin/users', $spec['paths']);
    }

    /** @test */
    public function it_displays_statistics()
    {
        $this->artisan('api-docs:generate', ['--output' => $this->outputPath])
            ->expectsOutput('Statistics:')
            ->expectsOutputToContain('Paths:')
            ->expectsOutputToContain('Operations:')
            ->expectsOutputToContain('Tags:')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_shows_progress_during_generation()
    {
        $this->artisan('api-docs:generate', ['--output' => $this->outputPath])
            ->expectsOutput('Scanning Laravel routes...')
            ->expectsOutput('Generating API specification...')
            ->assertExitCode(0);
    }
}
