<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403, 'Access denied. Admin privileges required.');
            }
            return $next($request);
        });
    }

    /**
     * Show the admin dashboard with user statistics.
     */
    public function dashboard()
    {
        // User statistics
        $totalUsers = User::count();
        $recentUsers = User::latest()->take(5)->get();
        $adminUsers = User::where('is_admin', true)->count();
        $regularUsers = User::where('is_admin', false)->count();
        
        // Quote Request statistics
        $totalQuoteRequests = \App\Models\QuoteRequest::count();
        $pendingQuotes = \App\Models\QuoteRequest::where('status', 'pending')->count();
        $quotedRequests = \App\Models\QuoteRequest::where('status', 'quoted')->count();
        $rejectedQuotes = \App\Models\QuoteRequest::where('status', 'rejected')->count();
        $recentQuoteRequests = \App\Models\QuoteRequest::with('customer')->latest()->take(5)->get();
        
        // Order statistics
        $totalOrders = \App\Models\Order::count();
        $activeOrders = \App\Models\Order::where('status', 'active')->count();
        $deliveredOrders = \App\Models\Order::where('status', 'delivered')->count();
        $completedOrders = \App\Models\Order::where('status', 'completed')->count();
        $overdueOrders = \App\Models\Order::where('status', 'active')
            ->where('due_date', '<', now())
            ->count();
        $recentOrders = \App\Models\Order::with(['customer', 'quoteRequest'])->latest()->take(5)->get();
        
        // Revenue statistics
        $totalRevenue = \App\Models\Order::where('status', 'completed')->sum('amount');
        $monthlyRevenue = \App\Models\Order::where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->sum('amount');
        $pendingRevenue = \App\Models\Order::whereIn('status', ['active', 'delivered'])->sum('amount');
        
        return view('admin.dashboard', compact(
            'totalUsers', 'recentUsers', 'adminUsers', 'regularUsers',
            'totalQuoteRequests', 'pendingQuotes', 'quotedRequests', 'rejectedQuotes', 'recentQuoteRequests',
            'totalOrders', 'activeOrders', 'deliveredOrders', 'completedOrders', 'overdueOrders', 'recentOrders',
            'totalRevenue', 'monthlyRevenue', 'pendingRevenue'
        ));
    }

    /**
     * Display a listing of all users.
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function createUser()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function storeUser(UpdateUserRequest $request)
    {
        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_admin' => $request->boolean('is_admin', false),
            ];

            User::create($data);

            return redirect()->route('admin.users')->with('success', 'User created successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'User creation failed. Please try again.'])->withInput();
        }
    }

    /**
     * Show the form for editing a user.
     */
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function updateUser(UpdateUserRequest $request, User $user)
    {
        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_admin' => $request->boolean('is_admin', false),
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            return redirect()->route('admin.users.edit', $user)->with('success', 'User updated successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'User update failed. Please try again.'])->withInput();
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function deleteUser(User $user)
    {
        try {
            $user->delete();
            return redirect()->route('admin.users')->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'User deletion failed. Please try again.']);
        }
    }
}
