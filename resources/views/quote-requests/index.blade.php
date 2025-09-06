@extends('layouts.dashboard')

@section('title', 'My Quote Requests')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">My Quote Requests</h1>
        <a href="{{ route('quote-requests.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
            New Quote Request
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('quote-requests.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="status" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status', 'all') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" name="date_from" id="date_from" 
                       value="{{ request('date_from') }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <!-- Date To -->
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                <input type="date" name="date_to" id="date_to" 
                       value="{{ request('date_to') }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    Apply Filters
                </button>
                <a href="{{ route('quote-requests.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    @if(request()->hasAny(['status', 'date_from', 'date_to']) && (request('status') !== 'all' || request('date_from') || request('date_to')))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <span class="font-medium">{{ $quoteRequests->total() }} results found</span>
                        @if(request('status') !== 'all' && request('status'))
                            for status: <span class="font-semibold">{{ $statuses[request('status')] }}</span>
                        @endif
                        @if(request('date_from') || request('date_to'))
                            @if(request('date_from') && request('date_to'))
                                from {{ \Carbon\Carbon::parse(request('date_from'))->format('M d, Y') }} to {{ \Carbon\Carbon::parse(request('date_to'))->format('M d, Y') }}
                            @elseif(request('date_from'))
                                from {{ \Carbon\Carbon::parse(request('date_from'))->format('M d, Y') }}
                            @elseif(request('date_to'))
                                up to {{ \Carbon\Carbon::parse(request('date_to'))->format('M d, Y') }}
                            @endif
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if($quoteRequests->count() > 0)
        <div class="grid gap-6">
            @foreach($quoteRequests as $request)
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 
                    @if($request->isPending()) border-yellow-500
                    @elseif($request->isQuoted()) border-green-500
                    @elseif($request->isAccepted()) border-blue-500
                    @else border-red-500
                    @endif">
                    
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">{{ $request->title }}</h3>
                            <p class="text-sm text-gray-600">{{ $request->request_number }} • {{ $request->created_at->format('M d, Y') }}</p>
                        </div>
                        
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            @if($request->isPending()) bg-yellow-100 text-yellow-800
                            @elseif($request->isQuoted()) bg-green-100 text-green-800
                            @elseif($request->isAccepted()) bg-blue-100 text-blue-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($request->status) }}
                        </span>
                    </div>

                    <p class="text-gray-700 mb-4">{{ Str::limit($request->instructions, 150) }}</p>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            @if($request->files && count($request->files) > 0)
                                <span class="text-sm text-gray-600">
                                    📎 {{ count($request->files) }} file(s) attached
                                </span>
                            @endif
                            
                            @if($request->isQuoted())
                                <span class="text-sm font-semibold text-green-600">
                                    Quote: ${{ number_format($request->quoted_amount, 2) }} • {{ $request->delivery_days }} days
                                </span>
                            @endif
                        </div>

                        <a href="{{ route('quote-requests.show', $request) }}" 
                           class="text-blue-600 hover:text-blue-800 font-medium">
                            View Details →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $quoteRequests->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-gray-400 mb-4">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-600 mb-2">No quote requests yet</h3>
            <p class="text-gray-500 mb-4">Start by creating your first quote request to get a custom quote.</p>
            <a href="{{ route('quote-requests.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                Create Quote Request
            </a>
        </div>
    @endif
</div>
@endsection
