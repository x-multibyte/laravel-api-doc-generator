<!DOCTYPE html>
<html lang="en" class="api-docs-static">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $description }}">
    <title>{{ $title }} - API Documentation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ $base_url }}/assets/css/api-docs.css" rel="stylesheet">
    <link rel="icon" href="{{ $base_url }}/assets/img/favicon.ico">
</head>
<body>
    <!-- Header -->
    <header class="api-docs-header text-center">
        <div class="container">
            <h1 class="display-4 mb-3">
                <i class="fas fa-book-open me-3"></i>
                {{ $title }}
            </h1>
            <p class="lead">{{ $description }}</p>
            <div class="mt-4">
                <span class="badge bg-light text-dark me-2">
                    <i class="fas fa-code-branch me-1"></i>
                    Version {{ $spec['info']['version'] ?? '1.0.0' }}
                </span>
                <span class="badge bg-light text-dark">
                    <i class="fas fa-file-code me-1"></i>
                    OpenAPI {{ $spec['openapi'] ?? '3.0.3' }}
                </span>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="api-docs-nav">
        <div class="container">
            <ul class="nav nav-pills justify-content-center">
                @foreach($themes as $theme)
                    <li class="nav-item">
                        <a href="{{ $theme }}.html" class="nav-link">
                            <i class="fas fa-{{ $this->getThemeIcon($theme) }} me-2"></i>
                            {{ $available_themes[$theme] ?? ucfirst($theme) }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-5">
        <div class="row">
            <!-- API Overview -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            API Overview
                        </h3>
                    </div>
                    <div class="card-body">
                        @if(isset($spec['info']['description']))
                            <p class="card-text">{{ $spec['info']['description'] }}</p>
                        @endif
                        
                        <div class="row mt-4">
                            <div class="col-sm-6">
                                <h6><i class="fas fa-server me-2"></i>Base URL</h6>
                                @if(isset($spec['servers'][0]['url']))
                                    <code>{{ $spec['servers'][0]['url'] }}</code>
                                @endif
                            </div>
                            <div class="col-sm-6">
                                <h6><i class="fas fa-shield-alt me-2"></i>Authentication</h6>
                                @if(isset($spec['components']['securitySchemes']))
                                    @foreach(array_keys($spec['components']['securitySchemes']) as $scheme)
                                        <span class="badge bg-secondary me-1">{{ $scheme }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">None required</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- API Statistics -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            API Statistics
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <h4 class="text-primary">{{ isset($spec['paths']) ? count($spec['paths']) : 0 }}</h4>
                                    <small class="text-muted">Endpoints</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <h4 class="text-success">{{ $this->countOperations($spec) }}</h4>
                                    <small class="text-muted">Operations</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <h4 class="text-warning">{{ $this->countTags($spec) }}</h4>
                                    <small class="text-muted">Tags</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <h4 class="text-info">{{ isset($spec['components']['schemas']) ? count($spec['components']['schemas']) : 0 }}</h4>
                                    <small class="text-muted">Schemas</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-download me-2"></i>
                            Downloads
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ $base_url }}/openapi.json" class="btn btn-outline-primary" download>
                                <i class="fas fa-file-code me-2"></i>
                                OpenAPI JSON
                            </a>
                            <a href="{{ $base_url }}/openapi.yaml" class="btn btn-outline-success" download>
                                <i class="fas fa-file-alt me-2"></i>
                                OpenAPI YAML
                            </a>
                        </div>
                    </div>
                </div>

                @if(isset($spec['info']['contact']) || isset($spec['info']['license']))
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-address-card me-2"></i>
                            Contact & License
                        </h3>
                    </div>
                    <div class="card-body">
                        @if(isset($spec['info']['contact']))
                            <h6>Contact</h6>
                            @if(isset($spec['info']['contact']['name']))
                                <p class="mb-1">{{ $spec['info']['contact']['name'] }}</p>
                            @endif
                            @if(isset($spec['info']['contact']['email']))
                                <p class="mb-1">
                                    <a href="mailto:{{ $spec['info']['contact']['email'] }}">
                                        {{ $spec['info']['contact']['email'] }}
                                    </a>
                                </p>
                            @endif
                            @if(isset($spec['info']['contact']['url']))
                                <p class="mb-3">
                                    <a href="{{ $spec['info']['contact']['url'] }}" target="_blank">
                                        {{ $spec['info']['contact']['url'] }}
                                    </a>
                                </p>
                            @endif
                        @endif

                        @if(isset($spec['info']['license']))
                            <h6>License</h6>
                            <p>
                                @if(isset($spec['info']['license']['url']))
                                    <a href="{{ $spec['info']['license']['url'] }}" target="_blank">
                                        {{ $spec['info']['license']['name'] }}
                                    </a>
                                @else
                                    {{ $spec['info']['license']['name'] }}
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="api-docs-footer">
        <div class="container">
            <p class="mb-0">
                Generated by Laravel API Documentation Generator
                <br>
                <small class="text-muted">
                    Last updated: {{ date('Y-m-d H:i:s') }}
                </small>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ $base_url }}/assets/js/api-docs.js"></script>
</body>
</html>

@php
function getThemeIcon($theme) {
    return match($theme) {
        'swagger' => 'code',
        'redoc' => 'book',
        'rapidoc' => 'bolt',
        'custom' => 'palette',
        default => 'file-alt'
    };
}

function countOperations($spec) {
    $count = 0;
    if (isset($spec['paths'])) {
        foreach ($spec['paths'] as $path) {
            if (is_array($path)) {
                $count += count(array_intersect_key($path, array_flip(['get', 'post', 'put', 'patch', 'delete', 'options', 'head'])));
            }
        }
    }
    return $count;
}

function countTags($spec) {
    $tags = [];
    if (isset($spec['paths'])) {
        foreach ($spec['paths'] as $path) {
            if (is_array($path)) {
                foreach ($path as $method => $operation) {
                    if (isset($operation['tags'])) {
                        $tags = array_merge($tags, $operation['tags']);
                    }
                }
            }
        }
    }
    return count(array_unique($tags));
}
@endphp
