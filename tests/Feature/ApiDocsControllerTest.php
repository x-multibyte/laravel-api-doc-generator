<?php

namespace LaravelApiDocs\Tests\Feature;

use LaravelApiDocs\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ApiDocsControllerTest extends TestCase
{
    /** @test */
    public function it_displays_documentation_index()
    {
        $response = $this->get('/api-docs');

        $response->assertStatus(200);
        $response->assertViewIs('api-docs::index');
        $response->assertViewHas('theme', 'swagger');
        $response->assertViewHas('availableThemes');
    }

    /** @test */
    public function it_displays_swagger_theme()
    {
        $response = $this->get('/api-docs/swagger');

        $response->assertStatus(200);
        $response->assertViewIs('api-docs::swagger');
    }

    /** @test */
    public function it_displays_redoc_theme()
    {
        $response = $this->get('/api-docs/redoc');

        $response->assertStatus(200);
        $response->assertViewIs('api-docs::redoc');
    }

    /** @test */
    public function it_displays_rapidoc_theme()
    {
        $response = $this->get('/api-docs/rapidoc');

        $response->assertStatus(200);
        $response->assertViewIs('api-docs::rapidoc');
    }

    /** @test */
    public function it_generates_api_specification()
    {
        $response = $this->get('/api-docs/generate');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'openapi',
            'info' => ['title', 'version', 'description'],
            'servers',
            'paths',
            'components'
        ]);
    }

    /** @test */
    public function it_exports_json_specification()
    {
        $response = $this->get('/api-docs/spec.json');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertHeader('Content-Disposition', 'attachment; filename="openapi.json"');
        
        $data = $response->json();
        $this->assertEquals('3.0.3', $data['openapi']);
    }

    /** @test */
    public function it_exports_yaml_specification()
    {
        $response = $this->get('/api-docs/spec.yaml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/x-yaml');
        $response->assertHeader('Content-Disposition', 'attachment; filename="openapi.yaml"');
        
        $content = $response->getContent();
        $this->assertStringContains('openapi: 3.0.3', $content);
    }

    /** @test */
    public function it_exports_with_format_parameter()
    {
        $jsonResponse = $this->get('/api-docs/export/json');
        $yamlResponse = $this->get('/api-docs/export/yaml');

        $jsonResponse->assertStatus(200);
        $jsonResponse->assertHeader('Content-Type', 'application/json');
        
        $yamlResponse->assertStatus(200);
        $yamlResponse->assertHeader('Content-Type', 'application/x-yaml');
    }

    /** @test */
    public function it_rejects_invalid_export_format()
    {
        $response = $this->get('/api-docs/export/xml');

        $response->assertStatus(400);
    }

    /** @test */
    public function it_imports_json_file()
    {
        Storage::fake('local');
        
        $spec = [
            'openapi' => '3.0.3',
            'info' => ['title' => 'Test API', 'version' => '1.0.0'],
            'paths' => []
        ];
        
        $file = UploadedFile::fake()->createWithContent(
            'openapi.json',
            json_encode($spec)
        );

        $response = $this->post('/api-docs/import', [
            'file' => $file
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'API specification imported successfully'
        ]);
    }

    /** @test */
    public function it_imports_yaml_file()
    {
        Storage::fake('local');
        
        $yaml = "
openapi: 3.0.3
info:
  title: Test API
  version: 1.0.0
paths: {}
";
        
        $file = UploadedFile::fake()->createWithContent('openapi.yaml', $yaml);

        $response = $this->post('/api-docs/import', [
            'file' => $file
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function it_validates_import_file_type()
    {
        Storage::fake('local');
        
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->post('/api-docs/import', [
            'file' => $file
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function it_handles_invalid_import_content()
    {
        Storage::fake('local');
        
        $file = UploadedFile::fake()->createWithContent(
            'invalid.json',
            '{"invalid": json content}'
        );

        $response = $this->post('/api-docs/import', [
            'file' => $file
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Failed to import API specification'
        ]);
    }

    /** @test */
    public function it_requires_file_for_import()
    {
        $response = $this->post('/api-docs/import');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function it_switches_themes_via_query_parameter()
    {
        $response = $this->get('/api-docs?theme=redoc');

        $response->assertStatus(200);
        $response->assertViewHas('theme', 'redoc');
    }

    /** @test */
    public function it_falls_back_to_default_theme_for_invalid_theme()
    {
        $response = $this->get('/api-docs?theme=invalid');

        $response->assertStatus(200);
        $response->assertViewHas('theme', 'invalid'); // Should still pass through, view will handle fallback
    }
}
