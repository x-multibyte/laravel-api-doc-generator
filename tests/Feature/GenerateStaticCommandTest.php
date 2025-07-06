<?php

namespace LaravelApiDocs\Tests\Feature;

use LaravelApiDocs\Tests\TestCase;
use Illuminate\Support\Facades\File;

class GenerateStaticCommandTest extends TestCase
{
    protected string $outputPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->outputPath = storage_path('api-docs-static-test');
        
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
    public function it_generates_static_documentation()
    {
        $this->artisan('api-docs:static', ['--output' => $this->outputPath])
            ->expectsOutput('Static Documentation Generator')
            ->expectsOutput('Static documentation generated successfully!')
            ->assertExitCode(0);

        // Check generated files
        $this->assertTrue(File::exists($this->outputPath . '/index.html'));
        $this->assertTrue(File::exists($this->outputPath . '/openapi.json'));
        $this->assertTrue(File::exists($this->outputPath . '/openapi.yaml'));
        $this->assertTrue(File::exists($this->outputPath . '/swagger.html'));
        $this->assertTrue(File::exists($this->outputPath . '/redoc.html'));
    }

    /** @test */
    public function it_generates_specific_themes()
    {
        $this->artisan('api-docs:static', [
            '--output' => $this->outputPath,
            '--themes' => ['swagger', 'redoc']
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($this->outputPath . '/swagger.html'));
        $this->assertTrue(File::exists($this->outputPath . '/redoc.html'));
        $this->assertFalse(File::exists($this->outputPath . '/rapidoc.html'));
        $this->assertFalse(File::exists($this->outputPath . '/custom.html'));
    }

    /** @test */
    public function it_copies_assets()
    {
        $this->artisan('api-docs:static', ['--output' => $this->outputPath])
            ->assertExitCode(0);

        $this->assertTrue(File::exists($this->outputPath . '/assets'));
        $this->assertTrue(File::exists($this->outputPath . '/assets/css/api-docs.css'));
        $this->assertTrue(File::exists($this->outputPath . '/assets/js/api-docs.js'));
        $this->assertTrue(File::exists($this->outputPath . '/assets/img/favicon.ico'));
    }

    /** @test */
    public function it_skips_assets_when_requested()
    {
        $this->artisan('api-docs:static', [
            '--output' => $this->outputPath,
            '--no-assets' => true
        ])->assertExitCode(0);

        $this->assertFalse(File::exists($this->outputPath . '/assets'));
    }

    /** @test */
    public function it_generates_sitemap()
    {
        config(['api-docs.static.generate_sitemap' => true]);

        $this->artisan('api-docs:static', ['--output' => $this->outputPath])
            ->expectsOutput('Generating sitemap...')
            ->assertExitCode(0);

        $this->assertTrue(File::exists($this->outputPath . '/sitemap.xml'));
        
        $sitemapContent = File::get($this->outputPath . '/sitemap.xml');
        $this->assertStringContains('<?xml version="1.0"', $sitemapContent);
        $this->assertStringContains('<urlset', $sitemapContent);
    }

    /** @test */
    public function it_minifies_html_when_requested()
    {
        $this->artisan('api-docs:static', [
            '--output' => $this->outputPath,
            '--minify' => true
        ])->assertExitCode(0);

        $indexContent = File::get($this->outputPath . '/index.html');
        
        // Minified HTML should have reduced whitespace
        $this->assertStringNotContains("\n    ", $indexContent);
    }

    /** @test */
    public function it_creates_output_directory()
    {
        $nonExistentPath = $this->outputPath . '/nested/deep/path';
        
        $this->artisan('api-docs:static', ['--output' => $nonExistentPath])
            ->assertExitCode(0);

        $this->assertTrue(File::exists($nonExistentPath));
    }

    /** @test */
    public function it_prompts_for_overwrite_confirmation()
    {
        // Create existing files
        File::makeDirectory($this->outputPath, 0755, true);
        File::put($this->outputPath . '/existing.txt', 'content');

        $this->artisan('api-docs:static', ['--output' => $this->outputPath])
            ->expectsQuestion('Output directory contains files. Overwrite?', true)
            ->assertExitCode(0);
    }

    /** @test */
    public function it_forces_overwrite_with_force_option()
    {
        // Create existing files
        File::makeDirectory($this->outputPath, 0755, true);
        File::put($this->outputPath . '/existing.txt', 'content');

        $this->artisan('api-docs:static', [
            '--output' => $this->outputPath,
            '--force' => true
        ])->assertExitCode(0);

        // Should generate without prompting
        $this->assertTrue(File::exists($this->outputPath . '/index.html'));
    }

    /** @test */
    public function it_displays_generation_statistics()
    {
        $this->artisan('api-docs:static', ['--output' => $this->outputPath])
            ->expectsOutput('Generated Files:')
            ->expectsOutput('Summary:')
            ->expectsOutputToContain('Total Files:')
            ->expectsOutputToContain('Total Size:')
            ->expectsOutput('Next Steps:')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_validates_themes()
    {
        $this->artisan('api-docs:static', [
            '--output' => $this->outputPath,
            '--themes' => ['invalid-theme']
        ])
        ->expectsOutput('No valid themes specified')
        ->assertExitCode(1);
    }
}
