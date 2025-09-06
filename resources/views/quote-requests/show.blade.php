@extends('layouts.dashboard')

@section('title', 'Quote Request Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">{{ $quoteRequest->title }}</h1>
                    <p class="text-gray-600 mt-1">{{ $quoteRequest->request_number }} • Submitted on {{ $quoteRequest->created_at->format('M d, Y \a\t g:i A') }}</p>
                </div>
                
                <span class="px-4 py-2 rounded-full text-sm font-medium
                    @if($quoteRequest->isPending()) bg-yellow-100 text-yellow-800
                    @elseif($quoteRequest->isQuoted()) bg-green-100 text-green-800
                    @elseif($quoteRequest->isAccepted()) bg-blue-100 text-blue-800
                    @else bg-red-100 text-red-800
                    @endif">
                    {{ ucfirst($quoteRequest->status) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Project Details -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Project Details</h2>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 whitespace-pre-line">{{ $quoteRequest->instructions }}</p>
                    </div>
                </div>

                <!-- Attached Files -->
                @if($quoteRequest->files && count($quoteRequest->files) > 0)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Attached Files</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($quoteRequest->files as $index => $file)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            @if(in_array($file['type'], ['image/jpeg', 'image/jpg', 'image/png']))
                                                <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            @else
                                                <svg class="h-8 w-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $file['name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ number_format($file['size'] / 1024 / 1024, 2) }} MB</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('quote-requests.download-file', [$quoteRequest, $index]) }}" 
                                       class="text-blue-600 hover:text-blue-800">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Quote Response -->
                @if($quoteRequest->isQuoted() || $quoteRequest->isAccepted() || $quoteRequest->status === 'rejected')
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Our Response</h2>
                        
                        @if($quoteRequest->status === 'rejected')
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">Quote Request Declined</h3>
                                        @if($quoteRequest->quote_notes)
                                            <div class="mt-2 text-sm text-red-700">
                                                <p>{{ $quoteRequest->quote_notes }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h3 class="text-lg font-semibold text-green-800 mb-2">Quote Amount</h3>
                                        <p class="text-3xl font-bold text-green-600">${{ number_format($quoteRequest->quoted_amount, 2) }}</p>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-green-800 mb-2">Delivery Time</h3>
                                        <p class="text-xl font-semibold text-green-600">{{ $quoteRequest->delivery_days }} days</p>
                                    </div>
                                </div>
                                
                                @if($quoteRequest->quote_notes)
                                    <div class="mt-4 pt-4 border-t border-green-200">
                                        <h4 class="text-sm font-medium text-green-800 mb-2">Additional Notes:</h4>
                                        <p class="text-sm text-green-700">{{ $quoteRequest->quote_notes }}</p>
                                    </div>
                                @endif
                                
                                @if($quoteRequest->quoted_at)
                                    <div class="mt-4 pt-4 border-t border-green-200">
                                        <p class="text-xs text-green-600">Quote provided on {{ $quoteRequest->quoted_at->format('M d, Y \a\t g:i A') }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Status Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Status</h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="ml-3 text-sm text-gray-700">Request Submitted</span>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-6 h-6 {{ $quoteRequest->isQuoted() || $quoteRequest->isAccepted() || $quoteRequest->status === 'rejected' ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                                @if($quoteRequest->isQuoted() || $quoteRequest->isAccepted() || $quoteRequest->status === 'rejected')
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                @endif
                            </div>
                            <span class="ml-3 text-sm text-gray-700">Quote Provided</span>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-6 h-6 {{ $quoteRequest->isAccepted() ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                                @if($quoteRequest->isAccepted())
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                @endif
                            </div>
                            <span class="ml-3 text-sm text-gray-700">Quote Accepted</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                @if($quoteRequest->isQuoted())
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Actions</h3>
                        <div class="space-y-3">
                            <a id="pay-now-inline" data-amount="{{ $quoteRequest->quoted_amount }}" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition inline-block text-center font-semibold">
                                Accept Quote & Pay ${{ number_format($quoteRequest->quoted_amount, 2) }}
                            </a>
                        </div>
                        <p class="text-xs text-gray-500 text-center mt-4">
                            Secure payment processing by 2Checkout
                        </p>
                    </div>
                @elseif($quoteRequest->isAccepted() && $quoteRequest->order)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Created</h3>
                        <p class="text-sm text-gray-600 mb-4">Your quote has been accepted and an order has been created.</p>
                        <a href="{{ route('orders.show', $quoteRequest->order) }}" 
                           class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition inline-block text-center">
                            View Order Details
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-8">
            <a href="{{ route('quote-requests.index') }}" 
               class="text-gray-600 hover:text-gray-800">
                ← Back to Quote Requests
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($quoteRequest->isQuoted())
<script src="https://secure.2checkout.com/checkout/client/twoCoInlineCart.js"></script>
<script>
(function(){
  const btn = document.querySelector('#pay-now-inline');
  if(!btn) return;
  btn.addEventListener('click', function(e){
    e.preventDefault();
    try {
      const rawAmount = parseFloat(btn.getAttribute('data-amount')) || 0;
      const amount = (rawAmount % 1 === 0) ? String(parseInt(rawAmount,10)) : rawAmount.toFixed(2);
      let productName = @json(preg_replace('/[^A-Za-z0-9 ]+/', ' ', $quoteRequest->title ?: 'Digitizing Service'));
      productName = (productName || 'Digitizing Service').trim() || 'Digitizing Service';

      if(typeof TwoCoInlineCart === 'undefined') {
        alert('Payment library failed to load. Please retry.');
        return;
      }

      // ---- 2Checkout / Verifone Inline Cart Initialization ----
      const merchantCode = @json((config('services.twocheckout.account_number') ?? env('TWOCHECKOUT_ACCOUNT_NUMBER')));
      if(!merchantCode){
        console.error('Missing merchant code (TWOCHECKOUT_ACCOUNT_NUMBER).');
        alert('Payment temporarily unavailable.');
        return;
      }
      TwoCoInlineCart.setup.setMerchant(merchantCode);
      TwoCoInlineCart.setup.setMode('DYNAMIC');
      // Optional: Pre-load iframe faster (immediate | delayed)
      // TwoCoInlineCart.setup.setIframeLoad('immediate');

      // Register services (since we only loaded the library script, not the full CP snippet)
      if(typeof TwoCoInlineCart.register === 'function') {
        TwoCoInlineCart.register();
      }

      // Reset previous cart state to avoid stale sessions when navigating across quotes
      if(TwoCoInlineCart.cart && typeof TwoCoInlineCart.cart.setReset === 'function') {
        TwoCoInlineCart.cart.setReset(true);
      }

      // Currency
      TwoCoInlineCart.cart.setCurrency('USD');

      // Remove previous products (safety)
      TwoCoInlineCart.products.removeAll();

      // Add dynamic product
      TwoCoInlineCart.products.add({
        type: 'PRODUCT',
        name: productName,
        price: amount,
        quantity: '1',
        tangible: false
      });

      // Set external references to reconcile later server-side
      if(TwoCoInlineCart.cart.setOrderExternalRef){
        TwoCoInlineCart.cart.setOrderExternalRef('QUOTE-' + @json($quoteRequest->id));
      }
      if(TwoCoInlineCart.cart.setCustomerReference && @json(auth()->id())){
        TwoCoInlineCart.cart.setCustomerReference('USER-' + @json(auth()->id()));
      }

      // Prefill billing (non-sensitive identification only)
      @if(auth()->check())
        if(TwoCoInlineCart.billing){
          const billing = TwoCoInlineCart.billing;
          billing.setName(@json(auth()->user()->name));
          billing.setEmail(@json(auth()->user()->email));
          @if(auth()->user()->phone ?? false)
            billing.setPhone(@json(preg_replace('/\D+/', '', auth()->user()->phone)));
          @endif
          // Auto advance if all mandatory data set
          if(TwoCoInlineCart.cart.setAutoAdvance){
            TwoCoInlineCart.cart.setAutoAdvance(true);
          }
        }
      @endif

      // Return method after successful payment (you should implement signature / webhook validation server-side)
            const returnUrl = new URL(@json(url('/thank-you')));
            returnUrl.searchParams.set('quote_id', @json($quoteRequest->id));
            returnUrl.searchParams.set('refnoext', 'QUOTE-' + @json($quoteRequest->id));
            TwoCoInlineCart.cart.setReturnMethod({
                type: 'redirect',
                url: returnUrl.toString()
            });

      // Subscribe to events to trigger backend acknowledgment
      if(TwoCoInlineCart.events && typeof TwoCoInlineCart.events.subscribe === 'function') {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        TwoCoInlineCart.events.subscribe('payment:finalized', function(data){
          // Send lightweight notification to server (implement route & verification separately)
          fetch(@json(route('payments.inline.finalized', [], false)), {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
              quote_id: @json($quoteRequest->id),
              order_external_ref: 'QUOTE-' + @json($quoteRequest->id),
              user_id: @json(auth()->id()),
              payment_event: 'payment:finalized',
              payload: data || null
            })
          }).catch(err=>console.warn('Finalize callback failed', err));
        });
      }

      // Sandbox / Test mode
      @if(config('services.twocheckout.sandbox'))
        if(TwoCoInlineCart.cart.setTest){
          TwoCoInlineCart.cart.setTest(true);
        }
      @endif

      // Start checkout
    try { if(window.localStorage){ localStorage.setItem('last_quote_id', String(@json($quoteRequest->id))); } } catch(e){}
      TwoCoInlineCart.cart.checkout();

    } catch(err){
      console.error('Inline cart error', err);
      alert('Unable to start checkout.');
    }
  });
})();
</script>
@endif
@endpush
