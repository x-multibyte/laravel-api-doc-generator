<div class="custom-api-docs">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-1/4 bg-gray-50 border-r border-gray-200 overflow-y-auto">
            <div class="p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">API Endpoints</h3>
                <div id="endpoint-list" class="space-y-2">
                    <!-- Endpoints will be loaded here -->
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="p-6">
                <div id="endpoint-details" class="space-y-6">
                    <div class="text-center text-gray-500 py-12">
                        <i class="fas fa-mouse-pointer text-4xl mb-4"></i>
                        <p>Select an endpoint from the sidebar to view details</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let apiSpec = null;

// Load API specification
fetch('{{ route("api-docs.spec.json") }}')
    .then(response => response.json())
    .then(data => {
        apiSpec = data;
        renderEndpointList();
    })
    .catch(error => {
        console.error('Error loading API spec:', error);
    });

function renderEndpointList() {
    const endpointList = document.getElementById('endpoint-list');
    endpointList.innerHTML = '';
    
    if (!apiSpec || !apiSpec.paths) return;
    
    Object.keys(apiSpec.paths).forEach(path => {
        const pathItem = apiSpec.paths[path];
        
        Object.keys(pathItem).forEach(method => {
            if (['get', 'post', 'put', 'patch', 'delete'].includes(method)) {
                const operation = pathItem[method];
                const endpointDiv = document.createElement('div');
                endpointDiv.className = 'cursor-pointer p-3 rounded-md hover:bg-gray-100 border border-gray-200';
                endpointDiv.onclick = () => showEndpointDetails(path, method, operation);
                
                const methodColor = getMethodColor(method);
                
                endpointDiv.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded ${methodColor} mr-2">
                                ${method.toUpperCase()}
                            </span>
                            <span class="text-sm font-medium text-gray-900">${path}</span>
                        </div>
                    </div>
                    <div class="mt-1 text-xs text-gray-600">
                        ${operation.summary || 'No summary available'}
                    </div>
                `;
                
                endpointList.appendChild(endpointDiv);
            }
        });
    });
}

function getMethodColor(method) {
    const colors = {
        'get': 'bg-blue-100 text-blue-800',
        'post': 'bg-green-100 text-green-800',
        'put': 'bg-yellow-100 text-yellow-800',
        'patch': 'bg-orange-100 text-orange-800',
        'delete': 'bg-red-100 text-red-800'
    };
    return colors[method] || 'bg-gray-100 text-gray-800';
}

function showEndpointDetails(path, method, operation) {
    const detailsContainer = document.getElementById('endpoint-details');
    const methodColor = getMethodColor(method);
    
    let parametersHtml = '';
    if (operation.parameters && operation.parameters.length > 0) {
        parametersHtml = `
            <div class="bg-gray-50 p-4 rounded-md">
                <h4 class="font-semibold text-gray-900 mb-3">Parameters</h4>
                <div class="space-y-2">
                    ${operation.parameters.map(param => `
                        <div class="flex items-center justify-between p-2 bg-white rounded border">
                            <div>
                                <span class="font-medium text-gray-900">${param.name}</span>
                                <span class="text-xs text-gray-500 ml-2">(${param.in})</span>
                                ${param.required ? '<span class="text-xs text-red-600 ml-1">*</span>' : ''}
                            </div>
                            <div class="text-sm text-gray-600">
                                ${param.schema?.type || 'string'}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    let requestBodyHtml = '';
    if (operation.requestBody) {
        requestBodyHtml = `
            <div class="bg-gray-50 p-4 rounded-md">
                <h4 class="font-semibold text-gray-900 mb-3">Request Body</h4>
                <div class="bg-white p-3 rounded border">
                    <pre class="text-sm text-gray-800 overflow-x-auto"><code>${JSON.stringify(operation.requestBody, null, 2)}</code></pre>
                </div>
            </div>
        `;
    }
    
    let responsesHtml = '';
    if (operation.responses) {
        responsesHtml = `
            <div class="bg-gray-50 p-4 rounded-md">
                <h4 class="font-semibold text-gray-900 mb-3">Responses</h4>
                <div class="space-y-2">
                    ${Object.keys(operation.responses).map(statusCode => `
                        <div class="bg-white p-3 rounded border">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-gray-900">${statusCode}</span>
                                <span class="text-sm text-gray-600">${operation.responses[statusCode].description}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    detailsContainer.innerHTML = `
        <div class="border-b border-gray-200 pb-4">
            <div class="flex items-center mb-2">
                <span class="inline-block px-3 py-1 text-sm font-semibold rounded ${methodColor} mr-3">
                    ${method.toUpperCase()}
                </span>
                <h2 class="text-2xl font-bold text-gray-900">${path}</h2>
            </div>
            <p class="text-gray-600">${operation.summary || 'No summary available'}</p>
            ${operation.description ? `<p class="text-gray-600 mt-2">${operation.description}</p>` : ''}
        </div>
        
        <div class="space-y-6">
            ${parametersHtml}
            ${requestBodyHtml}
            ${responsesHtml}
        </div>
        
        <div class="bg-blue-50 p-4 rounded-md">
            <h4 class="font-semibold text-blue-900 mb-2">Try it out</h4>
            <button onclick="tryEndpoint('${path}', '${method}')" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                <i class="fas fa-play mr-2"></i>Send Request
            </button>
        </div>
    `;
}

function tryEndpoint(path, method) {
    // This would implement the actual API testing functionality
    alert(`Testing ${method.toUpperCase()} ${path} - This feature would send a real request to your API`);
}
</script>

<style>
.custom-api-docs {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.custom-api-docs pre {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 12px;
    font-size: 13px;
    line-height: 1.4;
}

.custom-api-docs code {
    font-family: 'Fira Code', 'Monaco', 'Consolas', monospace;
}
</style>
