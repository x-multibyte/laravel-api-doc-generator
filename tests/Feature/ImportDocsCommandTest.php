<?php

namespace LaravelApiDocs\Tests\Feature;

use LaravelApiDocs\Tests\TestCase;
use Illuminate\Support\Facades\File;

class ImportDocsCommandTest extends TestCase
{
    protected string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/api-docs-test-' . uniqid();
        File::makeDirectory($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }
        parent::tearDown();
    }

    /** @test */
    public function it_imports_valid_json_file()
    {
        $spec = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Imported API',
                'version' => '1.0.0'
            ],
            'paths' => [
                '/test' => [
                    'get' => [
                        'summary' => 'Test endpoint',
                        'responses' => ['200' => ['description' => 'Success']]
                    ]
                ]
            ]
        ];

        $filePath = $this->tempDir . '/test.json';
        File::put($filePath, json_encode($spec, JSON_PRETTY_PRINT));

        $this->artisan('api-docs:import', ['file' => $filePath])
            ->expectsOutput('API Documentation Importer')
            ->expectsOutput('File parsed successfully')
            ->expectsOutput('API specification imported successfully!')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_imports_valid_yaml_file()
    {
        $yaml = "
openapi: 3.0.3
info:
  title: Imported YAML API
  version: 1.0.0
paths:
  /test:
    get:
      summary: Test endpoint
      responses:
        '200':
          description: Success
";

        $filePath = $this->tempDir . '/test.yaml';
        File::put($filePath, $yaml);

        $this->artisan('api-docs:import', ['file' => $filePath])
            ->expectsOutput('File parsed successfully')
            ->expectsOutput('API specification imported successfully!')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_validates_specification_when_requested()
    {
        $spec = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Valid API',
                'version' => '1.0.0'
            ],
            'paths' => [
                '/test' => [
                    'get' => [
                        'responses' => ['200' => ['description' => 'Success']]
                    ]
                ]
            ]
        ];

        $filePath = $this->tempDir . '/valid.json';
        File::put($filePath, json_encode($spec));

        $this->artisan('api-docs:import', [
            'file' => $filePath,
            '--validate' => true
        ])
        ->expectsOutput('Validating OpenAPI specification...')
        ->expectsOutput('Specification is valid')
        ->assertExitCode(0);
    }

    /** @test */
    public function it_handles_invalid_specification()
    {
        $invalidSpec = [
            'info' => [
                'title' => 'Invalid API'
                // Missing version and openapi fields
            ]
        ];

        $filePath = $this->tempDir . '/invalid.json';
        File::put($filePath, json_encode($invalidSpec));

        $this->artisan('api-docs:import', [
            'file' => $filePath,
            '--validate' => true
        ])
        ->expectsOutput('Specification validation failed:')
        ->expectsQuestion('Continue with invalid specification?', false)
        ->assertExitCode(1);
    }

    /** @test */
    public function it_creates_backup_when_requested()
    {
        $spec = [
            'openapi' => '3.0.3',
            'info' => ['title' => 'Test API', 'version' => '1.0.0'],
            'paths' => []
        ];

        $filePath = $this->tempDir . '/test.json';
        File::put($filePath, json_encode($spec));

        $this->artisan('api-docs:import', [
            'file' => $filePath,
            '--backup' => true
        ])
        ->expectsOutput('Creating backup of current specification...')
        ->assertExitCode(0);
    }

    /** @test */
    public function it_handles_nonexistent_file()
    {
        $this->artisan('api-docs:import', ['file' => '/nonexistent/file.json'])
            ->expectsOutput('File not found: /nonexistent/file.json')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_handles_unsupported_file_format()
    {
        $filePath = $this->tempDir . '/test.txt';
        File::put($filePath, 'some content');

        $this->artisan('api-docs:import', ['file' => $filePath])
            ->expectsOutput('Unsupported file format. Only JSON and YAML files are supported.')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_handles_invalid_json()
    {
        $filePath = $this->tempDir . '/invalid.json';
        File::put($filePath, '{"invalid": json}');

        $this->artisan('api-docs:import', ['file' => $filePath])
            ->expectsOutput('Failed to parse file:')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_displays_import_statistics()
    {
        $spec = [
            'openapi' => '3.0.3',
            'info' => ['title' => 'Test API', 'version' => '2.0.0'],
            'paths' => [
                '/users' => [
                    'get' => ['responses' => ['200' => ['description' => 'Success']]],
                    'post' => ['responses' => ['201' => ['description' => 'Created']]]
                ]
            ],
            'components' => [
                'schemas' => [
                    'User' => ['type' => 'object']
                ]
            ]
        ];

        $filePath = $this->tempDir . '/test.json';
        File::put($filePath, json_encode($spec));

        $this->artisan('api-docs:import', ['file' => $filePath])
            ->expectsOutput('Import Statistics:')
            ->expectsOutputToContain('Imported paths: 1')
            ->expectsOutputToContain('Imported operations: 2')
            ->expectsOutputToContain('API version: 2.0.0')
            ->expectsOutputToContain('API title: Test API')
            ->assertExitCode(0);
    }
}
