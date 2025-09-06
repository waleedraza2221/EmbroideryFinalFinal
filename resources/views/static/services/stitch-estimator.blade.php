@extends('layouts.landing')
@section('title','Stitch Estimator - {{ config("app.name") }}')
@section('content')

<div class="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
    <!-- Hero Section -->
    <div class="relative overflow-hidden pt-24 pb-16">
        <div class="absolute inset-0 bg-gradient-to-r from-purple-800/20 to-blue-800/20"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
                    Stitch <span class="bg-gradient-to-r from-yellow-400 to-orange-500 bg-clip-text text-transparent">Estimator</span>
                </h1>
                <p class="text-xl text-gray-200 max-w-3xl mx-auto mb-8">
                    Get accurate stitch count and pricing estimates for your embroidery projects instantly. 
                    Upload your design or use our interactive calculator.
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <div class="grid lg:grid-cols-2 gap-12">
            
            <!-- Interactive Calculator -->
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20" x-data="stitchCalculator()">
                <h2 class="text-2xl font-bold text-white mb-6">
                    <i class="fas fa-calculator mr-2 text-yellow-400"></i>
                    Interactive Calculator
                </h2>
                
                <div class="space-y-6">
                    <!-- Design Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-200 mb-2">Design Type</label>
                        <select x-model="designType" @change="calculateStitches()" 
                                class="w-full bg-white/10 border border-white/30 rounded-lg px-4 py-3 text-black focus:ring-2 focus:ring-yellow-400 focus:border-transparent">
                            <option value="simple">Simple Text/Logo</option>
                            <option value="medium">Medium Complexity</option>
                            <option value="complex">Complex Design</option>
                            <option value="photo">Photo Realistic</option>
                        </select>
                    </div>

                    <!-- Dimensions -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Width (inches)</label>
                            <input type="number" x-model="width" @input="calculateStitches()" step="0.1" min="0.5" max="15"
                                   class="w-full bg-white/10 border border-white/30 rounded-lg px-4 py-3 text-black focus:ring-2 focus:ring-yellow-400 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Height (inches)</label>
                            <input type="number" x-model="height" @input="calculateStitches()" step="0.1" min="0.5" max="15"
                                   class="w-full bg-white/10 border border-white/30 rounded-lg px-4 py-3 text-black focus:ring-2 focus:ring-yellow-400 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Number of Colors -->
                    <div>
                        <label class="block text-sm font-medium text-gray-200 mb-2">Number of Colors</label>
                        <input type="number" x-model="colors" @input="calculateStitches()" min="1" max="15"
                               class="w-full bg-white/10 border border-white/30 rounded-lg px-4 py-3 text-black focus:ring-2 focus:ring-yellow-400 focus:border-transparent">
                    </div>

                    <!-- Results -->
                    <div class="bg-gradient-to-r from-yellow-400/20 to-orange-500/20 rounded-lg p-6 border border-yellow-400/30">
                        <h3 class="text-lg font-semibold text-white mb-4">Estimation Results</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-yellow-400" x-text="estimatedStitches.toLocaleString()"></div>
                                <div class="text-sm text-gray-300">Estimated Stitches</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-400">$<span x-text="estimatedPrice.toFixed(2)"></span></div>
                                <div class="text-sm text-gray-300">Estimated Price</div>
                            </div>
                        </div>
                        <div class="mt-4 text-xs text-gray-400 text-center">
                            *Prices are estimates. Final pricing may vary based on design complexity.
                        </div>
                    </div>

                    <!-- Get Quote Button -->
                    <button onclick="window.location.href='{{ route('quote-requests.create') }}'"
                            class="w-full bg-gradient-to-r from-yellow-400 to-orange-500 hover:from-yellow-500 hover:to-orange-600 text-gray-900 font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105">
                        Get Detailed Quote
                    </button>
                </div>
            </div>

            <!-- File Upload Estimator -->
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20">
                <h2 class="text-2xl font-bold text-white mb-6">
                    <i class="fas fa-upload mr-2 text-yellow-400"></i>
                    Upload for Analysis
                </h2>
                
                <div x-data="fileUpload()">
                    <!-- Upload Area -->
                    <div class="border-2 border-dashed border-white/30 rounded-lg p-8 text-center mb-6"
                         @dragover.prevent
                         @drop.prevent="handleDrop($event)"
                         :class="{ 'border-yellow-400 bg-yellow-400/10': isDragging }"
                         @dragenter="isDragging = true"
                         @dragleave="isDragging = false">
                        
                        <div x-show="!uploadedFile">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-300 mb-2">Drag and drop your design file here</p>
                            <p class="text-sm text-gray-500 mb-4">or</p>
                            <label class="inline-block bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold py-2 px-6 rounded-lg cursor-pointer transition-colors">
                                Choose File
                                <input type="file" class="hidden" @change="handleFileSelect($event)" accept="image/*,.pdf">
                            </label>
                            <p class="text-xs text-gray-500 mt-2">Supports: JPG, PNG, PDF (Max 10MB)</p>
                        </div>

                        <div x-show="uploadedFile" class="text-white">
                            <i class="fas fa-check-circle text-green-400 text-2xl mb-2"></i>
                            <p x-text="uploadedFile?.name"></p>
                            <button @click="removeFile()" class="text-red-400 hover:text-red-300 text-sm mt-2">
                                Remove file
                            </button>
                        </div>
                    </div>

                    <!-- File Analysis Results -->
                    <div x-show="uploadedFile" class="bg-blue-500/20 rounded-lg p-6 border border-blue-400/30">
                        <h3 class="text-lg font-semibold text-white mb-4">
                            <i class="fas fa-search mr-2"></i>
                            Analysis Results
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-300">File Size:</span>
                                <span class="text-white" x-text="(uploadedFile?.size / 1024 / 1024).toFixed(2) + ' MB'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Detected Colors:</span>
                                <span class="text-white">Analyzing...</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Complexity:</span>
                                <span class="text-yellow-400">Medium</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Est. Stitches:</span>
                                <span class="text-green-400">8,500 - 12,000</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Est. Price:</span>
                                <span class="text-green-400">$8.50 - $12.00</span>
                            </div>
                        </div>
                        
                        <button onclick="window.location.href='{{ route('quote-requests.create') }}'"
                                class="w-full mt-4 bg-gradient-to-r from-blue-400 to-purple-500 hover:from-blue-500 hover:to-purple-600 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300">
                            Get Professional Quote
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Information -->
        <div class="mt-16 bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20">
            <h2 class="text-2xl font-bold text-white mb-8 text-center">
                <i class="fas fa-info-circle mr-2 text-yellow-400"></i>
                How We Calculate Stitch Counts
            </h2>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-yellow-400/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-ruler text-yellow-400 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Design Size</h3>
                    <p class="text-gray-300 text-sm">Larger designs require more stitches. We calculate based on square inches.</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-400/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-palette text-blue-400 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Color Count</h3>
                    <p class="text-gray-300 text-sm">Each color change adds setup time and complexity to the embroidery process.</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-400/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-cogs text-purple-400 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Complexity</h3>
                    <p class="text-gray-300 text-sm">Detailed designs require more precise stitch placement and higher stitch density.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function stitchCalculator() {
    return {
        designType: 'simple',
        width: 4,
        height: 3,
        colors: 3,
        estimatedStitches: 0,
        estimatedPrice: 0,
        
        init() {
            this.calculateStitches();
        },
        
        calculateStitches() {
            const area = this.width * this.height;
            const typeMultipliers = {
                'simple': 800,
                'medium': 1200,
                'complex': 1600,
                'photo': 2000
            };
            
            const baseStitches = area * typeMultipliers[this.designType];
            const colorMultiplier = 1 + (this.colors - 1) * 0.1;
            
            this.estimatedStitches = Math.round(baseStitches * colorMultiplier);
            
            // Price calculation: $1 per 1000 stitches
            this.estimatedPrice = (this.estimatedStitches / 1000) * 1;
        }
    }
}

function fileUpload() {
    return {
        uploadedFile: null,
        isDragging: false,
        
        handleDrop(event) {
            this.isDragging = false;
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                this.uploadedFile = files[0];
            }
        },
        
        handleFileSelect(event) {
            const files = event.target.files;
            if (files.length > 0) {
                this.uploadedFile = files[0];
            }
        },
        
        removeFile() {
            this.uploadedFile = null;
        }
    }
}
</script>

@endsection
