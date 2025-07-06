<div id="swagger-ui"></div>

<link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui.css" />
<script src="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui-bundle.js"></script>

<script>
window.onload = function() {
    const ui = SwaggerUIBundle({
        url: '{{ route("api-docs.spec.json") }}',
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [
            SwaggerUIBundle.presets.apis,
            SwaggerUIBundle.presets.standalone
        ],
        plugins: [
            SwaggerUIBundle.plugins.DownloadUrl
        ],
        layout: "StandaloneLayout",
        validatorUrl: null,
        tryItOutEnabled: true,
        supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
        onComplete: function() {
            console.log('Swagger UI loaded successfully');
        },
        onFailure: function(error) {
            console.error('Failed to load Swagger UI:', error);
        }
    });
};
</script>

<style>
    .swagger-ui .topbar {
        display: none;
    }
    
    .swagger-ui .info {
        margin: 20px 0;
    }
    
    .swagger-ui .scheme-container {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
</style>
