<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Legacy fallback: some 2Checkout setups send IPN to WordPress-style endpoint '?wc-api=2checkout_ipn_inline'
        if($request->isMethod('post') && $request->query('wc-api') === '2checkout_ipn_inline'){
            // Forward to PaymentController@webhook logic
            $paymentController = app(PaymentController::class);
            return $paymentController->webhook($request);
        }
        $testimonials = Cache::remember('landing:testimonials', 600, function(){
            return Testimonial::query()
                ->where('is_active', true)
                ->orderBy('display_order')
                ->take(9)
                ->get();
        });
        return view('landing', [
            'testimonials' => $testimonials,
        ]);
    }
}
