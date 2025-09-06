@extends('layouts.landing')
@section('title','Embroidery Format Converter - {{ config("app.name") }}')
@section('content')

<div class="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
    <!-- Hero Section -->
    <div class="relative overflow-hidden pt-24 pb-16">
        <div class="absolute inset-0 bg-gradient-to-r from-purple-800/20 to-blue-800/20"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
                    Format <span class="bg-gradient-to-r from-yellow-400 to-orange-500 bg-clip-text text-transparent">Converter</span>
                </h1>
                <p class="text-xl text-gray-200 max-w-3xl mx-auto mb-8">
                    Convert your embroidery files between different formats instantly. 
                    Support for DST, PES, JEF, EXP, VP3, and many more formats.
                </p>
            </div>
        </div>
    </div>

    <!-- Main Converter Section -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        
        <!-- Converter Tool -->
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20 mb-12" x-data="formatConverter()">
            <h2 class="text-2xl font-bold text-white mb-6 text-center">
                <i class="fas fa-exchange-alt mr-2 text-yellow-400"></i>
                Convert Your Embroidery Files
            </h2>
            
            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Upload Section -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">Upload Your File</h3>
                    
                    <!-- File Upload Area -->
                    <div class="border-2 border-dashed border-white/30 rounded-lg p-6 text-center mb-4"
                         @dragover.prevent
                         @drop.prevent="handleDrop($event)"
                         :class="{ 'border-yellow-400 bg-yellow-400/10': isDragging }"
                         @dragenter="isDragging = true"
                         @dragleave="isDragging = false">
                        
                        <div x-show="!uploadedFile">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-300 mb-2">Drag and drop your embroidery file here</p>
                            <p class="text-sm text-gray-500 mb-4">or</p>
                            <label class="inline-block bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold py-2 px-6 rounded-lg cursor-pointer transition-colors">
                                Choose File
                                <input type="file" class="hidden" @change="handleFileSelect($event)" accept=".dst,.pes,.jef,.exp,.vp3,.xxx,.pcs,.hus,.sew,.pec,.vip,.csd,.xxx">
                            </label>
                            <p class="text-xs text-gray-500 mt-2">Supports: DST, PES, JEF, EXP, VP3, XXX, PCS, HUS, SEW, PEC, VIP, CSD (Max 50MB)</p>
                        </div>

                        <div x-show="uploadedFile" class="text-white">
                            <i class="fas fa-file-alt text-green-400 text-3xl mb-2"></i>
                            <p class="font-medium" x-text="uploadedFile?.name"></p>
                            <p class="text-sm text-gray-400" x-text="(uploadedFile?.size / 1024 / 1024).toFixed(2) + ' MB'"></p>
                            <p class="text-sm text-green-400 mt-1">Ready to convert</p>
                            <button @click="removeFile()" class="text-red-400 hover:text-red-300 text-sm mt-2">
                                <i class="fas fa-times mr-1"></i> Remove file
                            </button>
                        </div>
                    </div>

                    <!-- Source Format Detection -->
                    <div x-show="uploadedFile" class="bg-blue-500/20 rounded-lg p-4 border border-blue-400/30">
                        <h4 class="text-white font-medium mb-2">
                            <i class="fas fa-search mr-2"></i>
                            File Analysis
                        </h4>
                        <div class="text-sm space-y-1">
                            <div class="flex justify-between">
                                <span class="text-gray-300">Format:</span>
                                <span class="text-yellow-400 uppercase" x-text="getFileExtension(uploadedFile?.name)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Size:</span>
                                <span class="text-white" x-text="(uploadedFile?.size / 1024).toFixed(1) + ' KB'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Status:</span>
                                <span class="text-green-400">Valid embroidery file</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conversion Options -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">Convert To</h3>
                    
                    <!-- Format Selection Grid -->
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <template x-for="format in outputFormats" :key="format.ext">
                            <button @click="selectedFormat = format.ext" 
                                    :class="selectedFormat === format.ext ? 'bg-yellow-400 text-gray-900' : 'bg-white/10 text-white hover:bg-white/20'"
                                    class="p-3 rounded-lg border border-white/30 transition-all duration-200 text-sm font-medium">
                                <div class="font-bold" x-text="format.ext.toUpperCase()"></div>
                                <div class="text-xs opacity-75" x-text="format.name"></div>
                            </button>
                        </template>
                    </div>

                    <!-- Convert Button -->
                    <button x-show="uploadedFile && selectedFormat" 
                            @click="convertFile()"
                            :disabled="isConverting"
                            :class="isConverting ? 'opacity-50 cursor-not-allowed' : 'hover:from-yellow-500 hover:to-orange-600 transform hover:scale-105'"
                            class="w-full bg-gradient-to-r from-yellow-400 to-orange-500 text-gray-900 font-semibold py-3 px-6 rounded-lg transition-all duration-300">
                        <span x-show="!isConverting">
                            <i class="fas fa-magic mr-2"></i>
                            Convert to <span x-text="selectedFormat?.toUpperCase()"></span>
                        </span>
                        <span x-show="isConverting">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Converting...
                        </span>
                    </button>

                    <!-- Download Section -->
                    <div x-show="convertedFile" class="mt-6 bg-green-500/20 rounded-lg p-4 border border-green-400/30">
                        <h4 class="text-white font-medium mb-3">
                            <i class="fas fa-check-circle text-green-400 mr-2"></i>
                            Conversion Complete!
                        </h4>
                        <div class="flex items-center justify-between">
                            <div class="text-sm">
                                <div class="text-gray-300">Converted to: <span class="text-green-400 font-medium" x-text="convertedFile?.format"></span></div>
                                <div class="text-gray-400">File: <span x-text="convertedFile?.name"></span></div>
                                <div class="text-gray-400">Size: <span x-text="(convertedFile?.size / 1024).toFixed(1) + ' KB'"></span></div>
                            </div>
                            <button @click="downloadFile()" class="bg-green-400 hover:bg-green-500 text-gray-900 font-semibold py-2 px-4 rounded-lg transition-colors">
                                <i class="fas fa-download mr-2"></i>
                                Download
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Supported Formats -->
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20 mb-12">
            <h2 class="text-2xl font-bold text-white mb-8 text-center">
                <i class="fas fa-list mr-2 text-yellow-400"></i>
                Supported Formats
            </h2>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-400/20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-file-code text-blue-400 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Machine Formats</h3>
                    <p class="text-gray-300 text-sm">DST, PES, JEF, EXP, VP3, XXX</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-400/20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-cogs text-green-400 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Industrial Formats</h3>
                    <p class="text-gray-300 text-sm">PCS, HUS, SEW, PEC, VIP, CSD</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-400/20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-magic text-purple-400 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">High Quality</h3>
                    <p class="text-gray-300 text-sm">Lossless conversion with stitch preservation</p>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20">
            <h2 class="text-2xl font-bold text-white mb-8 text-center">
                <i class="fas fa-star mr-2 text-yellow-400"></i>
                Why Choose Our Converter?
            </h2>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="w-12 h-12 bg-yellow-400/20 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-bolt text-yellow-400"></i>
                    </div>
                    <h3 class="text-white font-semibold mb-2">Fast Conversion</h3>
                    <p class="text-gray-300 text-sm">Convert files in seconds</p>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-blue-400/20 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-shield-alt text-blue-400"></i>
                    </div>
                    <h3 class="text-white font-semibold mb-2">Secure</h3>
                    <p class="text-gray-300 text-sm">Your files are safe</p>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-green-400/20 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-gem text-green-400"></i>
                    </div>
                    <h3 class="text-white font-semibold mb-2">High Quality</h3>
                    <p class="text-gray-300 text-sm">Preserve stitch data</p>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-purple-400/20 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-gift text-purple-400"></i>
                    </div>
                    <h3 class="text-white font-semibold mb-2">Free</h3>
                    <p class="text-gray-300 text-sm">No cost, no limits</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function formatConverter() {
    return {
        uploadedFile: null,
        isDragging: false,
        selectedFormat: '',
        isConverting: false,
        convertedFile: null,
        
        outputFormats: [
            { ext: 'dst', name: 'Tajima' },
            { ext: 'pes', name: 'Brother' },
            { ext: 'jef', name: 'Janome' },
            { ext: 'exp', name: 'Melco' },
            { ext: 'vp3', name: 'Husqvarna' },
            { ext: 'xxx', name: 'Singer' },
            { ext: 'pcs', name: 'Pfaff' },
            { ext: 'hus', name: 'Husqvarna' },
            { ext: 'sew', name: 'Janome' },
            { ext: 'pec', name: 'Brother' },
            { ext: 'vip', name: 'Pfaff' },
            { ext: 'csd', name: 'Singer' }
        ],
        
        init() {
            // Set default format
            this.selectedFormat = 'dst';
        },
        
        handleDrop(event) {
            this.isDragging = false;
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                this.validateAndSetFile(files[0]);
            }
        },
        
        handleFileSelect(event) {
            const files = event.target.files;
            if (files.length > 0) {
                this.validateAndSetFile(files[0]);
            }
        },
        
        validateAndSetFile(file) {
            // Check file size (50MB limit)
            if (file.size > 50 * 1024 * 1024) {
                alert('File size too large. Maximum size is 50MB.');
                return;
            }
            
            // Check file extension
            const validExtensions = ['.dst', '.pes', '.jef', '.exp', '.vp3', '.xxx', '.pcs', '.hus', '.sew', '.pec', '.vip', '.csd'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
            
            if (!validExtensions.includes(fileExtension)) {
                alert('Invalid file format. Please upload a valid embroidery file.');
                return;
            }
            
            this.uploadedFile = file;
            this.convertedFile = null;
        },
        
        removeFile() {
            this.uploadedFile = null;
            this.convertedFile = null;
        },
        
        getFileExtension(filename) {
            if (!filename) return '';
            return filename.split('.').pop().toLowerCase();
        },
        
        async convertFile() {
            if (!this.uploadedFile || !this.selectedFormat) return;
            
            this.isConverting = true;
            this.convertedFile = null;
            
            try {
                // Create FormData for file upload
                const formData = new FormData();
                formData.append('file', this.uploadedFile);
                formData.append('output_format', this.selectedFormat);
                
                // Add CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (csrfToken) {
                    formData.append('_token', csrfToken);
                }
                
                // Call conversion API
                const response = await fetch('/api/converter/convert', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
                
                const result = await response.json();
                
                if (!response.ok || !result.success) {
                    throw new Error(result.error || 'Conversion failed');
                }
                
                // Set converted file data
                this.convertedFile = {
                    download_id: result.data.download_id,
                    name: result.data.filename,
                    format: result.data.format,
                    size: result.data.size
                };
                
                // Show success message
                this.showMessage('Conversion completed successfully!', 'success');
                
            } catch (error) {
                console.error('Conversion error:', error);
                this.showMessage(error.message || 'Conversion failed. Please try again.', 'error');
            } finally {
                this.isConverting = false;
            }
        },
        
        downloadFile() {
            if (!this.convertedFile || !this.convertedFile.download_id) return;
            
            // Create download link
            const downloadUrl = `/api/converter/download/${this.convertedFile.download_id}`;
            
            // Trigger download
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = this.convertedFile.name;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Show success message
            this.showMessage('Download started!', 'success');
        },
        
        showMessage(message, type = 'info') {
            // Create and show a temporary message
            const messageDiv = document.createElement('div');
            messageDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white transition-all duration-300 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            messageDiv.textContent = message;
            
            document.body.appendChild(messageDiv);
            
            // Remove after 5 seconds
            setTimeout(() => {
                messageDiv.style.opacity = '0';
                setTimeout(() => {
                    if (messageDiv.parentNode) {
                        messageDiv.parentNode.removeChild(messageDiv);
                    }
                }, 300);
            }, 5000);
        }
    }
}
</script>

@endsection
