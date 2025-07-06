<div id="redoc-container"></div>

<script src="https://cdn.jsdelivr.net/npm/redoc@2.0.0/bundles/redoc.standalone.js"></script>

<script>
    Redoc.init('{{ route("api-docs.spec.json") }}', {
        scrollYOffset: 60,
        hideDownloadButton: false,
        theme: {
            colors: {
                primary: {
                    main: '#3b82f6'
                }
            },
            typography: {
                fontSize: '14px',
                lineHeight: '1.5em',
                code: {
                    fontSize: '13px',
                    fontFamily: 'Courier, monospace'
                },
                headings: {
                    fontFamily: 'Montserrat, sans-serif',
                    fontWeight: '600'
                }
            },
            sidebar: {
                width: '260px',
                backgroundColor: '#f8f9fa'
            }
        }
    }, document.getElementById('redoc-container'));
</script>

<style>
    #redoc-container {
        height: 100vh;
        overflow: auto;
    }
    
    .redoc-wrap {
        background-color: #ffffff;
    }
    
    .menu-content {
        background-color: #f8f9fa !important;
    }
</style>
