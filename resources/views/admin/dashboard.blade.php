@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                ← Back to User Dashboard
            </a>
        </div>
        <p class="text-gray-600 mt-2">Manage your business operations and monitor system performance.</p>
    </div>

    <!-- Overview Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalUsers }}</p>
                    <p class="text-xs text-gray-500">{{ $adminUsers }} admins, {{ $regularUsers }} customers</p>
                </div>
            </div>
        </div>

        <!-- Quote Requests -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Quote Requests</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalQuoteRequests }}</p>
                    <p class="text-xs text-gray-500">{{ $pendingQuotes }} pending review</p>
                </div>
            </div>
        </div>

        <!-- Active Orders -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Orders</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $activeOrders }}</p>
                    <p class="text-xs text-gray-500">{{ $overdueOrders }} overdue</p>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                    <p class="text-2xl font-semibold text-gray-900">${{ number_format($totalRevenue, 2) }}</p>
                    <p class="text-xs text-gray-500">${{ number_format($monthlyRevenue, 2) }} this month</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Management Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Quote Requests Management -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800">Quote Requests</h3>
                    <p class="text-sm text-gray-600">Review and respond to customer requests</p>
                </div>
            </div>
            
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Pending:</span>
                    <span class="font-medium text-yellow-600">{{ $pendingQuotes }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Quoted:</span>
                    <span class="font-medium text-green-600">{{ $quotedRequests }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Rejected:</span>
                    <span class="font-medium text-red-600">{{ $rejectedQuotes }}</span>
                </div>
            </div>
            
            <a href="{{ route('admin.quote-requests.index') }}" 
               class="w-full bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition inline-block text-center">
                Manage Quote Requests
            </a>
        </div>

        <!-- Orders Management -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800">Orders</h3>
                    <p class="text-sm text-gray-600">Fulfill orders and manage deliveries</p>
                </div>
            </div>
            
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Active:</span>
                    <span class="font-medium text-blue-600">{{ $activeOrders }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Delivered:</span>
                    <span class="font-medium text-green-600">{{ $deliveredOrders }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Completed:</span>
                    <span class="font-medium text-purple-600">{{ $completedOrders }}</span>
                </div>
            </div>
            
            <a href="{{ route('admin.orders.index') }}" 
               class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition inline-block text-center">
                Manage Orders
            </a>
        </div>

        <!-- User Management -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800">User Management</h3>
                    <p class="text-sm text-gray-600">Manage customer accounts and permissions</p>
                </div>
            </div>
            
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Total Users:</span>
                    <span class="font-medium text-blue-600">{{ $totalUsers }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Admins:</span>
                    <span class="font-medium text-purple-600">{{ $adminUsers }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Customers:</span>
                    <span class="font-medium text-green-600">{{ $regularUsers }}</span>
                </div>
            </div>
            
            <a href="{{ route('admin.users') }}" 
               class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition inline-block text-center">
                Manage Users
            </a>
        </div>

        <!-- Revenue Overview -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800">Revenue Overview</h3>
                    <p class="text-sm text-gray-600">Track earnings and financial metrics</p>
                </div>
            </div>
            
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Total:</span>
                    <span class="font-medium text-green-600">${{ number_format($totalRevenue, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">This Month:</span>
                    <span class="font-medium text-blue-600">${{ number_format($monthlyRevenue, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Pending:</span>
                    <span class="font-medium text-yellow-600">${{ number_format($pendingRevenue, 2) }}</span>
                </div>
            </div>
            
            <button class="w-full bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition" disabled>
                Revenue Reports
            </button>
        </div>

        <!-- System Settings -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800">System Settings</h3>
                    <p class="text-sm text-gray-600">Configure application settings</p>
                </div>
            </div>
            
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Status:</span>
                    <span class="font-medium text-green-600">Online</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Version:</span>
                    <span class="font-medium text-gray-600">v1.0.0</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Environment:</span>
                    <span class="font-medium text-blue-600">{{ app()->environment() }}</span>
                </div>
            </div>
            
            <button class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition" disabled>
                System Settings
            </button>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800">Quick Actions</h3>
                    <p class="text-sm text-gray-600">Common administrative tasks</p>
                </div>
            </div>
            
            <div class="space-y-3">
                <a href="{{ route('admin.users.create') }}" 
                   class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition inline-block text-center text-sm">
                    Add New User
                </a>
                <a href="{{ route('admin.quote-requests.index') }}?status=pending" 
                   class="w-full bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition inline-block text-center text-sm">
                    Review Pending Quotes
                </a>
                <a href="{{ route('admin.orders.index') }}?status=active" 
                   class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition inline-block text-center text-sm">
                    Check Active Orders
                </a>
            </div>
        </div>
    </div>
                        </div>
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="{{ route('admin.users') }}" class="block w-full text-left px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150">
                            Manage All Users
                        </a>
                        <a href="{{ route('admin.users.create') }}" class="block w-full text-left px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-150">
                            Create New User
                        </a>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">System Information</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Laravel Version:</span>
                            <span class="font-medium">{{ app()->version() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">PHP Version:</span>
                            <span class="font-medium">{{ PHP_VERSION }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Database:</span>
                            <span class="font-medium">PostgreSQL (Supabase)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Users</h2>
                @if($recentUsers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recentUsers as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->phone }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->created_at->format('M j, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">No users found.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
