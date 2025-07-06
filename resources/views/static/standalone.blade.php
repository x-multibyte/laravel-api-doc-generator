<!DOCTYPE html>
<html lang="en" class="api-docs-static">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $description }}">
    <title>{{ $title }} - {{ ucfirst($theme) }} Documentation</title>
    <link rel="icon" href="{{ $base_url }}/assets/img/favicon.ico">
    
    @if($theme === 'swagger')
        <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui.css" />
    @endif
    
    <style>
        body { margin: 0; padding: 0; font-family: sans-serif; }
        .header { background: #1f2937; color: white; padding: 1rem; text-align: center; }
        .header h1 { margin: 0; font-size: 1.5rem; }
        .back-link { position: absolute; top: 1rem; left: 1rem; color: white; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="header">
        <a href="{{ $base_url }}/index.html" class="back-link">← Back to Index</a>
        <h1>{{ $title }} - {{ ucfirst($theme) }}</h1>
    </div>

    @if($theme === 'swagger')
        <div id="swagger-ui"></div>
        <script src="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui-bundle.js"></script>
        <script>
            SwaggerUIBundle({
                url: '{{ $spec_url }}',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [SwaggerUIBundle.presets.apis, SwaggerUIBundle.presets.standalone],
                plugins: [SwaggerUIBundle.plugins.DownloadUrl],
                layout: "StandaloneLayout"
            });
        </script>
    @elseif($theme === 'redoc')
        <div id="redoc-container"></div>
        <script src="https://cdn.jsdelivr.net/npm/redoc@2.0.0/bundles/redoc.standalone.js"></script>
        <script>
            Redoc.init('{{ $spec_url }}', {
                scrollYOffset: 80,
                hideDownloadButton: false
            }, document.getElementById('redoc-container'));
        </script>
    @elseif($theme === 'rapidoc')
        <rapi-doc spec-url="{{ $spec_url }}" theme="light" render-style="read" allow-try="true"></rapi-doc>
        <script type="module" src="https://unpkg.com/rapidoc/dist/rapidoc-min.js"></script>
    @else
        @include('api-docs::themes.custom')
    @endif
</body>
</html>
