@extends('layouts.dashboard')

@section('title', 'Payment Confirmation - Thank You!')

@section('content')
<div class="container mx-auto px-4 py-10">
	<div class="max-w-3xl mx-auto">
		<div class="bg-white shadow rounded-lg p-8">
			<div class="flex items-start gap-4">
				<div class="h-14 w-14 flex items-center justify-center rounded-full bg-green-100">
					<svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
					</svg>
				</div>
				<div>
					<h1 class="text-2xl font-bold text-gray-900">Thank you!</h1>
					<p class="text-gray-600 mt-1">Your payment has been initiated. We're confirming it now.</p>
				</div>
			</div>

			<div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
				<div>
					<h2 class="text-sm font-semibold tracking-wide text-gray-500 uppercase mb-2">Summary</h2>
					<ul class="space-y-2 text-sm">
						<li class="flex justify-between"><span class="text-gray-600">Reference:</span><span id="pay-ref" class="font-medium text-gray-900">{{ $merchantOrderId ?? 'N/A' }}</span></li>
						<li class="flex justify-between"><span class="text-gray-600">Quote ID:</span><span class="font-medium text-gray-900">{{ $quoteId ?? 'N/A' }}</span></li>
						<li class="flex justify-between"><span class="text-gray-600">Payment Created:</span><span class="font-medium {{ $paymentCreated ? 'text-green-600' : 'text-red-600' }}">{{ $paymentCreated ? 'Yes' : 'No' }}</span></li>
						<li class="flex justify-between"><span class="text-gray-600">Status:</span><span id="pay-status" class="font-medium text-blue-600">Pending verification</span></li>
					</ul>
				</div>
				<div id="verification-section">
					<h2 class="text-sm font-semibold tracking-wide text-gray-500 uppercase mb-2">Verification Progress</h2>
					<div class="space-y-3">
						<div class="relative h-2 w-full bg-gray-200 rounded overflow-hidden">
							<div id="section-progress-bar" class="absolute left-0 top-0 h-full w-0 bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600 transition-all duration-500"></div>
						</div>
						<p id="section-progress-text" class="text-xs text-gray-500">Starting verification…</p>
						<p class="text-[11px] text-gray-400" id="attempt-label">Attempt <span id="attempt-num">0</span>/<span id="attempt-max">10</span></p>
						<div class="flex gap-2 pt-1">
							<button id="manual-refresh" type="button" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-medium">Refresh Now</button>
							<button id="stop-poll" type="button" class="px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white rounded text-xs font-medium">Stop</button>
						</div>
						<details id="raw-debug" class="mt-3 text-xs" open>
							<summary class="cursor-pointer select-none text-gray-600">Order API Raw (debug)</summary>
							<pre id="order-raw" class="mt-2 bg-gray-900 text-green-300 p-3 rounded overflow-x-auto max-h-64"></pre>
							<p class="mt-1 text-[10px] text-yellow-500">Do not expose raw payload in production.</p>
						</details>
					</div>
				</div>
			</div>

			<div class="mt-10 flex flex-wrap gap-4">
				<a href="{{ route('home') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium">Home</a>
				@auth
				<a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-gray-700 hover:bg-gray-800 text-white rounded-md text-sm font-medium">Dashboard</a>
				@endauth
			</div>

			<div class="mt-10 border-t pt-6 text-xs text-gray-500">
				If you close this page, the verification will continue in the background and you'll receive an email.
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 <script>
  $(document).ready(function () {
	  // Extract refno dynamically from query params (supports refno or REFNO)
	  var refNo = new URLSearchParams(window.location.search).get('refno') ||
				  new URLSearchParams(window.location.search).get('REFNO');
	  var quoteId = new URLSearchParams(window.location.search).get('quote_id') 
		  || new URLSearchParams(window.location.search).get('quote') 
		  || new URLSearchParams(window.location.search).get('refnoext');
	  // Fallback: try localStorage (if previously stored during quote accept)
	  if(!quoteId && window.localStorage){
		try { quoteId = localStorage.getItem('last_quote_id') || null; } catch(e){}
	  }
	  // Persist if newly discovered
	  if(quoteId && window.localStorage){
		try { localStorage.setItem('last_quote_id', quoteId); } catch(e){}
	  }
	  if(!refNo){
		  console.warn('No refno found in URL');
	  } else {
		  var refSpan = document.getElementById('pay-ref');
		  if(refSpan) refSpan.textContent = refNo;
	  }
      $.ajax({
          url: "/payments/order-status",
          method: "GET",
          data: {
			  refno: refNo,
              total: "1",
              "total-currency": "USD",
              signature: "eee8e395564ab6399065170b209627ff287f107176dca699f0a4cbd8ad4d56b1"
          },
          success: function (response) {
              console.log("✅ Order Status:", response);

			  // Attempt to detect COMPLETE status in upstream api_response
	      var status = (response.api_response && (response.api_response.Status || response.api_response.status)) || null;
			  if(status && status.toString().toUpperCase() === 'COMPLETE') {
				  // Call backend to finalize payment/order/invoice, then redirect
				  $.ajax({
					  url: "{{ route('payments.complete-from-client') }}",
					  method: 'POST',
					  headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
			      data: { refno: refNo, quote_id: quoteId, refnoext: new URLSearchParams(window.location.search).get('refnoext') },
					  success: function(fin){
						  console.log('Finalize response', fin);
						  if(fin && fin.ok && fin.order_id){
							  window.location.href = '/orders/' + fin.order_id;
						  } else {
							  console.warn('Finalize did not return order id; staying on page.');
						  }
					  },
					  error: function(xhr){
						  console.error('Finalize error', xhr.responseText || xhr.status);
					  }
				  });
			  } else {
				  $("body").append("<pre>" + JSON.stringify(response, null, 2) + "</pre>");
			  }
          },
          error: function (xhr, status, error) {
              console.error("❌ Error fetching order status:", error);
              $("body").append("<p style='color:red;'>Error fetching order status</p>");
          }
      });
  });
  </script>
@endpush

@push('scripts')
<script>


</script>
@endpush

