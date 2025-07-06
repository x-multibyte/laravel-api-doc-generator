<?php

namespace LaravelApiDocs\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use LaravelApiDocs\ApiDocsGenerator;

class ApiDocsController extends Controller
{
    protected $generator;

    public function __construct(ApiDocsGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function index()
    {
        $theme = request('theme', config('api-docs.default_theme'));
        $availableThemes = config('api-docs.available_themes');
        
        return view('api-docs::index', compact('theme', 'availableThemes'));
    }

    public function swagger()
    {
        return view('api-docs::swagger');
    }

    public function redoc()
    {
        return view('api-docs::redoc');
    }

    public function rapidoc()
    {
        return view('api-docs::rapidoc');
    }

    public function generate()
    {
        $spec = $this->generator->generate();
        return response()->json($spec);
    }

    public function specJson()
    {
        $json = $this->generator->exportToJson();
        
        return response($json)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="openapi.json"');
    }

    public function specYaml()
    {
        $yaml = $this->generator->exportToYaml();
        
        return response($yaml)
            ->header('Content-Type', 'application/x-yaml')
            ->header('Content-Disposition', 'attachment; filename="openapi.yaml"');
    }

    public function export($format)
    {
        if (!in_array($format, ['json', 'yaml'])) {
            abort(400, 'Invalid format. Supported formats: json, yaml');
        }

        if ($format === 'json') {
            return $this->specJson();
        } else {
            return $this->specYaml();
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json,yaml,yml',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getPathname());
        $extension = $file->getClientOriginalExtension();

        $success = false;
        if (in_array($extension, ['json'])) {
            $success = $this->generator->importFromJson($content);
        } elseif (in_array($extension, ['yaml', 'yml'])) {
            $success = $this->generator->importFromYaml($content);
        }

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'API specification imported successfully',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import API specification',
            ], 400);
        }
    }
}
