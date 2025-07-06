<div id="redoc-container"></div>

<script src="https://cdn.jsdelivr.net/npm/redoc@2.0.0/bundles/redoc.standalone.js"></script>
<script>
    Redoc.init('{{ $spec_url }}', {
        scrollYOffset: 60,
        hideDownloadButton: false
    }, document.getElementById('redoc-container'));
</script>
