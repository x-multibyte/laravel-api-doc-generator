<?php

namespace LaravelApiDocs\Tests\Feature;

use LaravelApiDocs\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class IntegrationTest extends TestCase
{
    /** @test */
    public function it_performs_complete_documentation_workflow()
    {
        $outputPath = storage_path('api-docs-integration-test');
        
        try {
            // 1. Generate documentation
            $this->artisan('api-docs:generate', [
                '--output' => $outputPath,
                '--format' => 'both',
                '--validate' => true
            ])->assertExitCode(0);

            // Verify files were created
            $this->assertTrue(File::exists($outputPath . '/openapi.json'));
            $this->assertTrue(File::exists($outputPath . '/openapi.yaml'));

            // 2. Check status
            $this->artisan('api-docs:status', ['--detailed' => true])
                ->assertExitCode(0);

            // 3. Test web interface
            $response = $this->get('/api-docs');
            $response->assertStatus(200);

            // 4. Test API endpoints
            $response = $this->get('/api-docs/spec.json');
            $response->assertStatus(200);
            
            $spec = $response->json();
            $this->assertEquals('3.0.3', $spec['openapi']);
            $this->assertArrayHasKey('paths', $spec);

            // 5. Test import functionality
            $importSpec = [
                'openapi' => '3.0.3',
                'info' => ['title' => 'Imported API', 'version' => '2.0.0'],
                'paths' => ['/imported' => ['get' => ['responses' => ['200' => ['description' => 'OK']]]]]
            ];
            
            $importFile = $outputPath . '/import_test.json';
            File::put($importFile, json_encode($importSpec));
            
            $this->artisan('api-docs:import', [
                'file' => $importFile,
                '--validate' => true
            ])->assertExitCode(0);

            // 6. Clean up
            $this->artisan('api-docs:clean', [
                '--generated' => true,
                '--dry-run' => true
            ])->assertExitCode(0);

        } finally {
            // Clean up test files
            if (File::exists($outputPath)) {
                File::deleteDirectory($outputPath);
            }
        }
    }

    /** @test */
    public function it_handles_large_api_with_many_routes()
    {
        // Add many test routes
        $this->app['router']->group(['prefix' => 'api'], function ($router) {
            for ($i = 1; $i <= 50; $i++) {
                $router->get("resource{$i}", "Resource{$i}Controller@index");
                $router->post("resource{$i}", "Resource{$i}Controller@store");
                $router->get("resource{$i}/{id}", "Resource{$i}Controller@show");
                $router->put("resource{$i}/{id}", "Resource{$i}Controller@update");
                $router->delete("resource{$i}/{id}", "Resource{$i}Controller@destroy");
            }
        });

        $outputPath = storage_path('api-docs-large-test');
        
        try {
            $this->artisan('api-docs:generate', [
                '--output' => $outputPath,
                '--validate' => true
            ])->assertExitCode(0);

            $content = File::get($outputPath . '/openapi.json');
            $spec = json_decode($content, true);
            
            // Should have many paths
            $this->assertGreaterThan(100, count($spec['paths']));
            
            // Should still be valid OpenAPI
            $this->assertEquals('3.0.3', $spec['openapi']);
            $this->assertArrayHasKey('info', $spec);
            
        } finally {
            if (File::exists($outputPath)) {
                File::deleteDirectory($outputPath);
            }
        }
    }

    /** @test */
    public function it_maintains_performance_with_complex_routes()
    {
        // Add complex routes with middleware, parameters, etc.
        $this->app['router']->group(['prefix' => 'api', 'middleware' => 'auth'], function ($router) {
            $router->get('complex/{id}/nested/{nested_id}/deep/{deep_id}', 'ComplexController@show');
            $router->post('complex/{id}/batch', 'ComplexController@batch');
            $router->group(['middleware' => 'admin'], function ($router) {
                $router->get('admin/complex/{id}/reports', 'AdminController@reports');
            });
        });

        $startTime = microtime(true);
        
        $spec = app('api-docs')->generate();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Should complete within reasonable time (adjust as needed)
        $this->assertLessThan(5.0, $executionTime, 'Documentation generation took too long');
        
        // Should still generate valid spec
        $this->assertArrayHasKey('paths', $spec);
        $this->assertArrayHasKey('/api/complex/{id}/nested/{nested_id}/deep/{deep_id}', $spec['paths']);
    }
}
