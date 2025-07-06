<?php

namespace LaravelApiDocs\Tests\Feature;

use LaravelApiDocs\Tests\TestCase;
use Illuminate\Support\Facades\File;

class CleanDocsCommandTest extends TestCase
{
    protected string $testDir;
    protected string $backupDir;
    protected string $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->testDir = storage_path('api-docs-test');
        $this->backupDir = $this->testDir . '/backups';
        $this->cacheDir = $this->testDir . '/cache';
        
        // Create test directories and files
        File::makeDirectory($this->backupDir, 0755, true);
        File::makeDirectory($this->cacheDir, 0755, true);
        
        // Create test backup files
        File::put($this->backupDir . '/openapi_backup_2024-01-01_10-00-00.json', '{}');
        File::put($this->backupDir . '/openapi_backup_2024-01-02_10-00-00.yaml', 'test: data');
        
        // Create test cache files
        File::put($this->cacheDir . '/route_cache.json', '{}');
        File::put($this->cacheDir . '/spec_cache.yaml', 'cached: data');
        
        // Create test generated files
        File::put($this->testDir . '/openapi.json', '{}');
        File::put($this->testDir . '/openapi.yaml', 'openapi: 3.0.3');
        File::put($this->testDir . '/imported_openapi.json', '{}');
    }

    protected function tearDown(): void
    {
        if (File::exists($this->testDir)) {
            File::deleteDirectory($this->testDir);
        }
        parent::tearDown();
    }

    /** @test */
    public function it_requires_cleanup_option()
    {
        $this->artisan('api-docs:clean')
            ->expectsOutput('Please specify what to clean: --backups, --cache, --generated, or --all')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_cleans_backup_files()
    {
        $this->artisan('api-docs:clean', ['--backups' => true])
            ->expectsOutput('API Documentation Cleaner')
            ->expectsOutput('Cleaning backup files...')
            ->expectsOutputToContain('openapi_backup_2024-01-01_10-00-00.json')
            ->expectsOutputToContain('openapi_backup_2024-01-02_10-00-00.yaml')
            ->assertExitCode(0);

        $this->assertFalse(File::exists($this->backupDir . '/openapi_backup_2024-01-01_10-00-00.json'));
        $this->assertFalse(File::exists($this->backupDir . '/openapi_backup_2024-01-02_10-00-00.yaml'));
    }

    /** @test */
    public function it_cleans_cache_files()
    {
        $this->artisan('api-docs:clean', ['--cache' => true])
            ->expectsOutput('Cleaning cache files...')
            ->expectsOutputToContain('route_cache.json')
            ->expectsOutputToContain('spec_cache.yaml')
            ->assertExitCode(0);

        $this->assertFalse(File::exists($this->cacheDir . '/route_cache.json'));
        $this->assertFalse(File::exists($this->cacheDir . '/spec_cache.yaml'));
    }

    /** @test */
    public function it_cleans_generated_files()
    {
        $this->artisan('api-docs:clean', ['--generated' => true])
            ->expectsOutput('Cleaning generated files...')
            ->expectsOutputToContain('openapi.json')
            ->expectsOutputToContain('openapi.yaml')
            ->expectsOutputToContain('imported_openapi.json')
            ->assertExitCode(0);

        $this->assertFalse(File::exists($this->testDir . '/openapi.json'));
        $this->assertFalse(File::exists($this->testDir . '/openapi.yaml'));
        $this->assertFalse(File::exists($this->testDir . '/imported_openapi.json'));
    }

    /** @test */
    public function it_cleans_all_files()
    {
        $this->artisan('api-docs:clean', ['--all' => true])
            ->expectsOutput('Cleaning backup files...')
            ->expectsOutput('Cleaning cache files...')
            ->expectsOutput('Cleaning generated files...')
            ->assertExitCode(0);

        $this->assertFalse(File::exists($this->backupDir . '/openapi_backup_2024-01-01_10-00-00.json'));
        $this->assertFalse(File::exists($this->cacheDir . '/route_cache.json'));
        $this->assertFalse(File::exists($this->testDir . '/openapi.json'));
    }

    /** @test */
    public function it_performs_dry_run_without_deleting()
    {
        $this->artisan('api-docs:clean', ['--all' => true, '--dry-run' => true])
            ->expectsOutput('DRY RUN MODE - No files will actually be deleted')
            ->expectsOutputToContain('Would delete')
            ->expectsOutput('Run without --dry-run to actually delete the files')
            ->assertExitCode(0);

        // Files should still exist
        $this->assertTrue(File::exists($this->backupDir . '/openapi_backup_2024-01-01_10-00-00.json'));
        $this->assertTrue(File::exists($this->cacheDir . '/route_cache.json'));
        $this->assertTrue(File::exists($this->testDir . '/openapi.json'));
    }

    /** @test */
    public function it_displays_cleanup_summary()
    {
        $this->artisan('api-docs:clean', ['--all' => true])
            ->expectsOutput('Cleanup Summary:')
            ->expectsOutputToContain('Files processed:')
            ->expectsOutputToContain('Space freed:')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_handles_nonexistent_directories()
    {
        // Remove test directories
        File::deleteDirectory($this->testDir);

        $this->artisan('api-docs:clean', ['--all' => true])
            ->expectsOutput('No backup directory found')
            ->expectsOutput('No cache directory found')
            ->expectsOutput('No documentation directory found')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_handles_empty_directories()
    {
        // Remove all files but keep directories
        File::cleanDirectory($this->backupDir);
        File::cleanDirectory($this->cacheDir);
        File::delete($this->testDir . '/openapi.json');
        File::delete($this->testDir . '/openapi.yaml');
        File::delete($this->testDir . '/imported_openapi.json');

        $this->artisan('api-docs:clean', ['--all' => true])
            ->expectsOutput('No files found to clean')
            ->assertExitCode(0);
    }
}
