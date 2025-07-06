<div class="custom-theme-container">
    <div id="api-documentation">
        <!-- Custom theme content will be loaded here -->
        <div class="loading">Loading API documentation...</div>
    </div>
</div>

<script>
// Load and render custom theme
fetch('{{ $spec_url }}')
    .then(response => response.json())
    .then(spec => {
        // Custom rendering logic here
        renderCustomDocumentation(spec);
    });

function renderCustomDocumentation(spec) {
    const container = document.getElementById('api-documentation');
    // Implement custom documentation rendering
    container.innerHTML = '<h1>' + spec.info.title + '</h1><p>' + spec.info.description + '</p>';
}
</script>
