<?php

use Illuminate\Support\Facades\Route;
use XMultibyte\ApiDoc\Http\Controllers\ApiDocsController;

Route::get('/', [ApiDocsController::class, 'index'])->name('api-docs.index');

// Theme routes
Route::get('/swagger', [ApiDocsController::class, 'swagger'])->name('api-docs.swagger');
Route::get('/redoc', [ApiDocsController::class, 'redoc'])->name('api-docs.redoc');
Route::get('/rapidoc', [ApiDocsController::class, 'rapidoc'])->name('api-docs.rapidoc');
Route::get('/custom', [ApiDocsController::class, 'custom'])->name('api-docs.custom');

// Specification routes
Route::get('/spec.json', [ApiDocsController::class, 'specJson'])->name('api-docs.spec.json');
Route::get('/spec.yaml', [ApiDocsController::class, 'specYaml'])->name('api-docs.spec.yaml');

// Management routes
Route::post('/import', [ApiDocsController::class, 'import'])->name('api-docs.import');
Route::get('/export', [ApiDocsController::class, 'export'])->name('api-docs.export');
Route::post('/generate-static', [ApiDocsController::class, 'generateStatic'])->name('api-docs.generate-static');
Route::get('/stats', [ApiDocsController::class, 'stats'])->name('api-docs.stats');
Route::post('/clear-cache', [ApiDocsController::class, 'clearCache'])->name('api-docs.clear-cache');
