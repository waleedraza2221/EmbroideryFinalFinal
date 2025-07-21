<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\QuoteRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the user dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get recent quote requests
        $recentQuoteRequests = QuoteRequest::where('customer_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();
        
        // Get recent orders
        $recentOrders = Order::where('customer_id', $user->id)
            ->with('quoteRequest')
            ->latest()
            ->limit(5)
            ->get();
        
        // Get statistics
        $stats = [
            'total_quote_requests' => QuoteRequest::where('customer_id', $user->id)->count(),
            'pending_quotes' => QuoteRequest::where('customer_id', $user->id)->where('status', 'pending')->count(),
            'active_orders' => Order::where('customer_id', $user->id)->where('status', 'active')->count(),
            'completed_orders' => Order::where('customer_id', $user->id)->where('status', 'completed')->count(),
        ];

        return view('dashboard', compact('user', 'recentQuoteRequests', 'recentOrders', 'stats'));
    }

    /**
     * Show the user profile edit form.
     */
    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    /**
     * Update the user's basic profile information.
     */
    public function updateBasic(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'phone' => 'required|string|unique:users,phone,' . Auth::id(),
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            $user = Auth::user();
            
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            return redirect()->route('profile.edit')->with('success', 'Basic information updated successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Basic information update failed. Please try again.'])->withInput();
        }
    }

    /**
     * Update the user's billing information.
     */
    public function updateBilling(Request $request)
    {
        $request->validate([
            'billing_name' => 'nullable|string|max:255',
            'billing_company' => 'nullable|string|max:255',
            'billing_address' => 'nullable|string|max:500',
            'billing_city' => 'nullable|string|max:255',
            'billing_state' => 'nullable|string|max:255',
            'billing_zip' => 'nullable|string|max:20',
            'billing_country' => 'nullable|string|max:255',
            'billing_tax_id' => 'nullable|string|max:255',
        ]);

        try {
            $user = Auth::user();
            
            $data = [
                'billing_name' => $request->billing_name,
                'billing_company' => $request->billing_company,
                'billing_address' => $request->billing_address,
                'billing_city' => $request->billing_city,
                'billing_state' => $request->billing_state,
                'billing_zip' => $request->billing_zip,
                'billing_country' => $request->billing_country,
                'billing_tax_id' => $request->billing_tax_id,
            ];

            $user->update($data);

            return redirect()->route('profile.edit')->with('success', 'Billing information updated successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Billing information update failed. Please try again.'])->withInput();
        }
    }

    /**
     * Update the user's profile information (legacy method for backward compatibility).
     */
    public function update(UpdateUserRequest $request)
    {
        try {
            $user = Auth::user();
            
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                // Billing Information
                'billing_company' => $request->billing_company,
                'billing_address_line_1' => $request->billing_address_line_1,
                'billing_address_line_2' => $request->billing_address_line_2,
                'billing_city' => $request->billing_city,
                'billing_state' => $request->billing_state,
                'billing_postal_code' => $request->billing_postal_code,
                'billing_country' => $request->billing_country,
                'billing_tax_id' => $request->billing_tax_id,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            return redirect()->route('profile.edit')->with('success', 'Profile updated successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Profile update failed. Please try again.'])->withInput();
        }
    }
}
