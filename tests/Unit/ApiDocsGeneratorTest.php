<?php

namespace XMultibyte\ApiDoc\Tests\Unit;

use XMultibyte\ApiDoc\Tests\TestCase;
use XMultibyte\ApiDoc\ApiDocsGenerator;
use Illuminate\Support\Facades\Route;

class ApiDocsGeneratorTest extends TestCase
{
    protected ApiDocsGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = app('api-docs');
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(ApiDocsGenerator::class, $this->generator);
    }

    /** @test */
    public function it_generates_basic_openapi_specification()
    {
        $spec = $this->generator->generate();

        $this->assertIsArray($spec);
        $this->assertEquals('3.0.3', $spec['openapi']);
        $this->assertEquals('Test API', $spec['info']['title']);
        $this->assertEquals('1.0.0', $spec['info']['version']);
        $this->assertEquals('Test API Description', $spec['info']['description']);
        $this->assertArrayHasKey('paths', $spec);
        $this->assertArrayHasKey('components', $spec);
    }

    /** @test */
    public function it_scans_api_routes_correctly()
    {
        $spec = $this->generator->generate();
        $paths = $spec['paths'];

        // Should include API routes
        $this->assertArrayHasKey('/api/users', $paths);
        $this->assertArrayHasKey('/api/users/{id}', $paths);
        $this->assertArrayHasKey('/api/posts', $paths);
        $this->assertArrayHasKey('/api/admin/users', $paths);

        // Should not include non-API routes
        $this->assertArrayNotHasKey('/web/home', $paths);
        $this->assertArrayNotHasKey('/telescope/requests', $paths);
    }

    /** @test */
    public function it_generates_correct_http_methods()
    {
        $spec = $this->generator->generate();
        $usersPaths = $spec['paths']['/api/users'];

        $this->assertArrayHasKey('get', $usersPaths);
        $this->assertArrayHasKey('post', $usersPaths);
        $this->assertArrayNotHasKey('put', $usersPaths);
        $this->assertArrayNotHasKey('delete', $usersPaths);

        $userByIdPaths = $spec['paths']['/api/users/{id}'];
        $this->assertArrayHasKey('get', $userByIdPaths);
        $this->assertArrayHasKey('put', $userByIdPaths);
        $this->assertArrayHasKey('delete', $userByIdPaths);
    }

    /** @test */
    public function it_generates_path_parameters()
    {
        $spec = $this->generator->generate();
        $userByIdGet = $spec['paths']['/api/users/{id}']['get'];

        $this->assertArrayHasKey('parameters', $userByIdGet);
        $this->assertCount(1, $userByIdGet['parameters']);
        
        $parameter = $userByIdGet['parameters'][0];
        $this->assertEquals('id', $parameter['name']);
        $this->assertEquals('path', $parameter['in']);
        $this->assertTrue($parameter['required']);
        $this->assertEquals('string', $parameter['schema']['type']);
    }

    /** @test */
    public function it_generates_request_body_for_post_methods()
    {
        $spec = $this->generator->generate();
        $usersPost = $spec['paths']['/api/users']['post'];

        $this->assertArrayHasKey('requestBody', $usersPost);
        $this->assertTrue($usersPost['requestBody']['required']);
        $this->assertArrayHasKey('application/json', $usersPost['requestBody']['content']);
    }

    /** @test */
    public function it_generates_responses()
    {
        $spec = $this->generator->generate();
        $usersGet = $spec['paths']['/api/users']['get'];

        $this->assertArrayHasKey('responses', $usersGet);
        $this->assertArrayHasKey('200', $usersGet['responses']);
        $this->assertArrayHasKey('400', $usersGet['responses']);
        $this->assertArrayHasKey('401', $usersGet['responses']);
        $this->assertArrayHasKey('404', $usersGet['responses']);
        $this->assertArrayHasKey('500', $usersGet['responses']);
    }

    /** @test */
    public function it_detects_authentication_requirements()
    {
        $spec = $this->generator->generate();
        
        // Regular routes should not have security
        $usersGet = $spec['paths']['/api/users']['get'];
        $this->assertArrayNotHasKey('security', $usersGet);
        
        // Auth protected routes should have security
        $adminUsersGet = $spec['paths']['/api/admin/users']['get'];
        $this->assertArrayHasKey('security', $adminUsersGet);
        $this->assertEquals([['bearerAuth' => []]], $adminUsersGet['security']);
    }

    /** @test */
    public function it_generates_tags_from_controllers()
    {
        $spec = $this->generator->generate();
        $usersGet = $spec['paths']['/api/users']['get'];

        $this->assertArrayHasKey('tags', $usersGet);
        $this->assertContains('Test', $usersGet['tags']);
    }

    /** @test */
    public function it_exports_to_json()
    {
        $json = $this->generator->exportToJson();
        
        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('3.0.3', $decoded['openapi']);
        $this->assertEquals('Test API', $decoded['info']['title']);
    }

    /** @test */
    public function it_exports_to_yaml()
    {
        $yaml = $this->generator->exportToYaml();
        
        $this->assertIsString($yaml);
        $this->assertStringContains('openapi: 3.0.3', $yaml);
        $this->assertStringContains('title: Test API', $yaml);
    }

    /** @test */
    public function it_imports_from_json()
    {
        $testSpec = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Imported API',
                'version' => '2.0.0'
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

        $json = json_encode($testSpec);
        $result = $this->generator->importFromJson($json);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_imports_from_yaml()
    {
        $yaml = "
openapi: 3.0.3
info:
  title: Imported YAML API
  version: 2.0.0
paths:
  /test:
    get:
      summary: Test endpoint
      responses:
        '200':
          description: Success
";

        $result = $this->generator->importFromYaml($yaml);
        $this->assertTrue($result);
    }

    /** @test */
    public function it_handles_invalid_json_import()
    {
        $invalidJson = '{"invalid": json}';
        $result = $this->generator->importFromJson($invalidJson);
        
        $this->assertFalse($result);
    }

    /** @test */
    public function it_handles_invalid_yaml_import()
    {
        $invalidYaml = "invalid:\n  - yaml\n    - structure";
        $result = $this->generator->importFromYaml($invalidYaml);
        
        $this->assertFalse($result);
    }

    /** @test */
    public function it_generates_summaries_from_routes()
    {
        $spec = $this->generator->generate();
        $usersGet = $spec['paths']['/api/users']['get'];
        $usersPost = $spec['paths']['/api/users']['post'];

        $this->assertStringContains('Get', $usersGet['summary']);
        $this->assertStringContains('Post', $usersPost['summary']);
    }

    /** @test */
    public function it_includes_servers_configuration()
    {
        $spec = $this->generator->generate();
        
        $this->assertArrayHasKey('servers', $spec);
        $this->assertIsArray($spec['servers']);
        $this->assertNotEmpty($spec['servers']);
    }

    /** @test */
    public function it_includes_security_schemes()
    {
        $spec = $this->generator->generate();
        
        $this->assertArrayHasKey('components', $spec);
        $this->assertArrayHasKey('securitySchemes', $spec['components']);
        $this->assertArrayHasKey('bearerAuth', $spec['components']['securitySchemes']);
        $this->assertArrayHasKey('apiKey', $spec['components']['securitySchemes']);
    }
}
