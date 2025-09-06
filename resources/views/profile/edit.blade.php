@extends('layouts.dashboard')

@section('title', 'Edit Profile')

@section('content')
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900">Edit Profile</h1>
            <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-500">
                ← Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Basic Information Form -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Basic Information</h2>
            </div>

            <form action="{{ route('profile.update.basic') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="name" id="name" 
                               class="mt-1 block w-full px-3 py-2 border @error('name') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="email" id="email" 
                               class="mt-1 block w-full px-3 py-2 border @error('email') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" name="phone" id="phone" 
                               class="mt-1 block w-full px-3 py-2 border @error('phone') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                               value="{{ old('phone', $user->phone) }}" required>
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                        <input type="password" name="password" id="password" 
                               class="mt-1 block w-full px-3 py-2 border @error('password') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                               placeholder="Leave blank to keep current password">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Leave blank if you don't want to change your password</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                               placeholder="Confirm new password">
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Update Basic Information
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Billing Information Form -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Billing Information</h2>
                <span class="text-sm text-gray-500">Optional - for invoicing purposes</span>
            </div>

            <form action="{{ route('profile.update.billing') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Billing Name -->
                    <div>
                        <label for="billing_name" class="block text-sm font-medium text-gray-700">Billing Name</label>
                        <input type="text" name="billing_name" id="billing_name" 
                               class="mt-1 block w-full px-3 py-2 border @error('billing_name') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                               value="{{ old('billing_name', $user->billing_name) }}"
                               placeholder="Name for billing/invoices">
                        @error('billing_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Company -->
                    <div>
                        <label for="billing_company" class="block text-sm font-medium text-gray-700">Company (Optional)</label>
                        <input type="text" name="billing_company" id="billing_company" 
                               class="mt-1 block w-full px-3 py-2 border @error('billing_company') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                               value="{{ old('billing_company', $user->billing_company) }}"
                               placeholder="Company name">
                        @error('billing_company')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address -->
                    <div class="md:col-span-2">
                        <label for="billing_address" class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="billing_address" id="billing_address" rows="3"
                                  class="mt-1 block w-full px-3 py-2 border @error('billing_address') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                  placeholder="Street address, apartment, suite, unit, building, floor, etc.">{{ old('billing_address', $user->billing_address) }}</textarea>
                        @error('billing_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- City -->
                    <div>
                        <label for="billing_city" class="block text-sm font-medium text-gray-700">City</label>
                        <input type="text" name="billing_city" id="billing_city" 
                               class="mt-1 block w-full px-3 py-2 border @error('billing_city') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                               value="{{ old('billing_city', $user->billing_city) }}">
                        @error('billing_city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- State -->
                    <div>
                        <label for="billing_state" class="block text-sm font-medium text-gray-700">State/Province</label>
                        <input type="text" name="billing_state" id="billing_state" 
                               class="mt-1 block w-full px-3 py-2 border @error('billing_state') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                               value="{{ old('billing_state', $user->billing_state) }}">
                        @error('billing_state')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Postal Code -->
                    <div>
                        <label for="billing_zip" class="block text-sm font-medium text-gray-700">Postal Code</label>
                        <input type="text" name="billing_zip" id="billing_zip" 
                               class="mt-1 block w-full px-3 py-2 border @error('billing_zip') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                               value="{{ old('billing_zip', $user->billing_zip) }}">
                        @error('billing_zip')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Country -->
                    <div>
                        <label for="billing_country" class="block text-sm font-medium text-gray-700">Country</label>
                        <select name="billing_country" id="billing_country" 
                                class="mt-1 block w-full px-3 py-2 border @error('billing_country') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">Select Country</option>
                            <option value="United States" {{ old('billing_country', $user->billing_country) === 'United States' ? 'selected' : '' }}>United States</option>
                            <option value="Canada" {{ old('billing_country', $user->billing_country) === 'Canada' ? 'selected' : '' }}>Canada</option>
                            <option value="United Kingdom" {{ old('billing_country', $user->billing_country) === 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                            <option value="Australia" {{ old('billing_country', $user->billing_country) === 'Australia' ? 'selected' : '' }}>Australia</option>
                            <option value="Germany" {{ old('billing_country', $user->billing_country) === 'Germany' ? 'selected' : '' }}>Germany</option>
                            <option value="France" {{ old('billing_country', $user->billing_country) === 'France' ? 'selected' : '' }}>France</option>
                            <option value="Other" {{ old('billing_country', $user->billing_country) === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('billing_country')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tax ID -->
                    <div class="md:col-span-2">
                        <label for="billing_tax_id" class="block text-sm font-medium text-gray-700">Tax ID / VAT Number (Optional)</label>
                        <input type="text" name="billing_tax_id" id="billing_tax_id" 
                               class="mt-1 block w-full px-3 py-2 border @error('billing_tax_id') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                               value="{{ old('billing_tax_id', $user->billing_tax_id) }}"
                               placeholder="Enter your business tax ID or VAT number">
                        @error('billing_tax_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">For business customers only</p>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Update Billing Information
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
