<rapi-doc 
    spec-url="{{ route('api-docs.spec.json') }}"
    theme="light"
    render-style="read"
    nav-bg-color="#f8f9fa"
    nav-text-color="#374151"
    nav-hover-bg-color="#e5e7eb"
    primary-color="#3b82f6"
    secondary-color="#6b7280"
    bg-color="#ffffff"
    text-color="#111827"
    header-color="#1f2937"
    regular-font="'Inter', sans-serif"
    mono-font="'Fira Code', monospace"
    font-size="default"
    allow-try="true"
    allow-server-selection="true"
    allow-authentication="true"
    allow-spec-url-load="false"
    allow-spec-file-load="false"
    show-header="true"
    show-info="true"
    show-components="true"
    info-description-headings-in-navbar="true"
    use-path-in-nav-bar="false"
    nav-item-spacing="default"
    default-schema-tab="schema"
    response-area-height="400px"
    fill-request-fields-with-example="true"
    persist-auth="false">
    
    <div slot="nav-logo" style="display: flex; align-items: center; justify-content: center; padding: 16px;">
        <i class="fas fa-book" style="color: #3b82f6; font-size: 24px; margin-right: 8px;"></i>
        <span style="font-weight: 600; color: #1f2937;">{{ config('api-docs.title') }}</span>
    </div>
    
</rapi-doc>

<script type="module" src="https://unpkg.com/rapidoc/dist/rapidoc-min.js"></script>

<style>
    rapi-doc {
        height: 100vh;
        width: 100%;
    }
    
    rapi-doc::part(section-navbar) {
        background-color: #f8f9fa;
        border-right: 1px solid #e5e7eb;
    }
    
    rapi-doc::part(section-main-content) {
        background-color: #ffffff;
    }
    
    rapi-doc::part(btn-try) {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }
    
    rapi-doc::part(btn-try:hover) {
        background-color: #2563eb;
        border-color: #2563eb;
    }
</style>
