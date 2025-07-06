<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('api-docs.title') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <i class="fas fa-book text-blue-600 text-2xl mr-3"></i>
                        <h1 class="text-2xl font-bold text-gray-900">{{ config('api-docs.title') }}</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Theme Selector -->
                        <select id="themeSelector" class="border border-gray-300 rounded-md px-3 py-2 bg-white">
                            @foreach($availableThemes as $key => $name)
                                <option value="{{ $key }}" {{ $theme === $key ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        
                        <!-- Export Buttons -->
                        <div class="flex space-x-2">
                            <a href="{{ route('api-docs.export', 'json') }}" 
                               class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                <i class="fas fa-download mr-2"></i>Export JSON
                            </a>
                            <a href="{{ route('api-docs.export', 'yaml') }}" 
                               class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                                <i class="fas fa-download mr-2"></i>Export YAML
                            </a>
                        </div>
                        
                        <!-- Import Button -->
                        <button onclick="openImportModal()" 
                                class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition-colors">
                            <i class="fas fa-upload mr-2"></i>Import
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-900">API Documentation</h2>
                    <p class="text-gray-600 mt-2">{{ config('api-docs.description') }}</p>
                </div>
                
                <!-- Theme Content -->
                <div id="documentationContent" class="p-6">
                    @if($theme === 'swagger')
                        @include('api-docs::themes.swagger')
                    @elseif($theme === 'redoc')
                        @include('api-docs::themes.redoc')
                    @elseif($theme === 'rapidoc')
                        @include('api-docs::themes.rapidoc')
                    @else
                        @include('api-docs::themes.custom')
                    @endif
                </div>
            </div>
        </main>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Import API Specification</h3>
                        <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <form id="importForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select OpenAPI file (JSON or YAML)
                            </label>
                            <input type="file" name="file" accept=".json,.yaml,.yml" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeImportModal()" 
                                    class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Theme switching
        document.getElementById('themeSelector').addEventListener('change', function() {
            const theme = this.value;
            window.location.href = `{{ route('api-docs.index') }}?theme=${theme}`;
        });

        // Import modal functions
        function openImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
        }

        // Import form handling
        document.getElementById('importForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('{{ route("api-docs.import") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('API specification imported successfully!');
                    closeImportModal();
                    location.reload();
                } else {
                    alert('Failed to import: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during import');
            });
        });
    </script>
</body>
</html>
