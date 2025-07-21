<?php

if (!function_exists('twocheckout_url')) {
    /**
     * Generate direct 2Checkout payment URL
     */
    function twocheckout_url($quoteRequest)
    {
        return \App\Http\Controllers\PaymentController::generateDirectPaymentURL($quoteRequest);
    }
}

if (!function_exists('twocheckout_simple_url')) {
    /**
     * Generate simple 2Checkout payment URL without signature
     */
    function twocheckout_simple_url($quoteRequest)
    {
        // Clean product title for URL
        $cleanTitle = preg_replace('/[^a-zA-Z0-9\s]/', '', $quoteRequest->title);
        $cleanTitle = trim($cleanTitle);
        if (empty($cleanTitle)) {
            $cleanTitle = 'Embroidery Product';
        }

        // Build simple 2Checkout URL without signature
        $baseUrl = 'https://secure.2checkout.com/checkout/buy';
        $params = [
            'merchant' => config('services.twocheckout.account_number'),
            'currency' => 'USD',
            'tpl' => 'default',
            'prod' => $cleanTitle,
            'price' => $quoteRequest->quoted_amount,
            'qty' => '1',
            'return-url' => route('payment.thankyou'),
            'test' => '1'
        ];

        return $baseUrl . '?' . http_build_query($params);
    }
}

if (!function_exists('twocheckout_convertplus_payload_url')) {
    /**
     * Generate URL for ConvertPlus payload endpoint
     */
    function twocheckout_convertplus_payload_url($quoteRequest)
    {
        return route('payment.convertplus.payload', $quoteRequest);
    }
}
