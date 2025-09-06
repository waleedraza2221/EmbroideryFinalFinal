@extends('layouts.dashboard')

@section('title', 'Create Quote Request')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Request a Quote</h1>
            <p class="text-gray-600 mt-2">Tell us about your project and get a custom quote within 24 hours.</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('quote-requests.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Project Title *
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror"
                           placeholder="e.g., Custom Logo Embroidery"
                           required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="instructions" class="block text-sm font-medium text-gray-700 mb-2">
                        Project Instructions *
                    </label>
                    <textarea id="instructions" 
                              name="instructions" 
                              rows="6"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('instructions') border-red-500 @enderror"
                              placeholder="Please describe your project in detail including:&#10;- Size and dimensions&#10;- Colors and design preferences&#10;- Material specifications&#10;- Any special requirements"
                              required>{{ old('instructions') }}</textarea>
                    @error('instructions')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="files" class="block text-sm font-medium text-gray-700 mb-2">
                        Attach Files (Optional)
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-md p-6 text-center hover:border-gray-400 transition">
                        <input type="file" 
                               id="files" 
                               name="files[]" 
                               multiple
                               class="hidden"
                               onchange="updateFileList(this)">
                        
                        <div id="file-drop-area" onclick="document.getElementById('files').click()">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p class="mt-2 text-gray-600">
                                <span class="text-blue-600 hover:text-blue-700 cursor-pointer">Click to upload files</span>
                                or drag and drop
                            </p>
                            <p class="text-xs text-gray-500">Any file type up to 10MB each</p>
                        </div>
                        
                        <div id="file-list" class="mt-4 text-left hidden"></div>
                    </div>
                    @error('files.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">What happens next?</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>We'll review your request within 24 hours</li>
                                    <li>You'll receive a detailed quote with pricing and timeline</li>
                                    <li>Once accepted, we'll start working on your project</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('quote-requests.index') }}" 
                       class="text-gray-600 hover:text-gray-800">
                        ← Back to Quote Requests
                    </a>
                    
                    <button type="submit" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                        Submit Quote Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateFileList(input) {
    const fileList = document.getElementById('file-list');
    const fileListContainer = fileList.parentElement;
    
    if (input.files.length > 0) {
        fileList.classList.remove('hidden');
        fileList.innerHTML = '<h4 class="font-medium text-gray-700 mb-2">Selected Files:</h4>';
        
        Array.from(input.files).forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'flex items-center justify-between bg-gray-50 p-2 rounded text-sm';
            fileItem.innerHTML = `
                <span class="text-gray-700">${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                <button type="button" onclick="removeFile(${index})" class="text-red-600 hover:text-red-800">×</button>
            `;
            fileList.appendChild(fileItem);
        });
    } else {
        fileList.classList.add('hidden');
    }
}

function removeFile(index) {
    const input = document.getElementById('files');
    const dt = new DataTransfer();
    
    Array.from(input.files).forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    input.files = dt.files;
    updateFileList(input);
}
</script>
@endsection
