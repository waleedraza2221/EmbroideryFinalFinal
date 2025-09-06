<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS CDN with dark mode class strategy -->
    <script>
        window.tailwind = window.tailwind || {}; 
        tailwind.config = { 
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Figtree', 'ui-sans-serif', 'system-ui']
                    }
                }
            }
        };
        (function(){
            const stored = localStorage.getItem('theme');
            const prefers = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if(stored === 'dark' || (!stored && prefers)){
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @stack('head')
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">
    <!-- Layout Compatibility Notice -->
    <div class="min-h-screen flex items-center justify-center p-8">
        <div class="max-w-2xl mx-auto text-center">
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-8 shadow-lg">
                <div class="w-16 h-16 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-yellow-800 dark:text-yellow-200 mb-4">Layout Migration Notice</h2>
                <div class="text-yellow-700 dark:text-yellow-300 space-y-4">
                    <p>This page is using the legacy <code class="bg-yellow-200 dark:bg-yellow-800 px-2 py-1 rounded text-sm">layouts.app</code> layout.</p>
                    <p>Please update your view to use one of the new layouts:</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-yellow-200 dark:border-yellow-700">
                            <h3 class="font-semibold text-indigo-600 dark:text-indigo-400 mb-2">Landing Layout</h3>
                            <code class="text-xs text-gray-600 dark:text-gray-300">@extends('layouts.landing')</code>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">For marketing pages, services, about, contact</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-yellow-200 dark:border-yellow-700">
                            <h3 class="font-semibold text-indigo-600 dark:text-indigo-400 mb-2">Dashboard Layout</h3>
                            <code class="text-xs text-gray-600 dark:text-gray-300">@extends('layouts.dashboard')</code>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">For authenticated user pages</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-yellow-200 dark:border-yellow-700">
                            <h3 class="font-semibold text-indigo-600 dark:text-indigo-400 mb-2">Auth Layout</h3>
                            <code class="text-xs text-gray-600 dark:text-gray-300">@extends('layouts.auth')</code>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">For login, register pages</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-center space-x-4">
                    @auth
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-500 transition-colors">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('home') }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-500 transition-colors">
                            Go to Home
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Page Content (Fallback) -->
    <div style="display: none;">
        @yield('content')
    </div>

    <!-- Scripts -->
    <script>
        window.toggleTheme = function(){
            const el = document.documentElement;
            const dark = el.classList.toggle('dark');
            localStorage.setItem('theme', dark ? 'dark' : 'light');
        };
    </script>
    
    @stack('scripts')
</body>
</html>
                                        <a href="{{ route('services.vector') }}" class="flex px-4 py-2 hover:bg-indigo-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">Vector Tracing</a>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">About</a>
                            <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
                                <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">Users</a>
                                <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">Orders</a>
                                <a href="{{ route('admin.invoices.index') }}" class="nav-link {{ request()->routeIs('admin.invoices*') ? 'active' : '' }}">Invoices</a>
                                <a href="{{ route('admin.testimonials.index') }}" class="nav-link {{ request()->routeIs('admin.testimonials*') ? 'active' : '' }}">Testimonials</a>
                            @else
                                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                                <a href="{{ route('quote-requests.index') }}" class="nav-link {{ request()->routeIs('quote-requests*') ? 'active' : '' }}">Quotes</a>
                                <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders*') ? 'active' : '' }}">Orders</a>
                                <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices*') ? 'active' : '' }}">Invoices</a>
                            @endif
                        </div>
                    </div>
                    <div class="hidden md:flex items-center space-x-4">
                        @include('partials.notifications')
                        <div class="text-sm font-medium text-gray-700">{{ Auth::user()->name }}</div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-md text-sm font-medium text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 transition">Logout</button>
                        </form>
                        <button type="button" onclick="window.toggleTheme()" class="ml-2 px-3 py-1.5 rounded-md text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-gray-700 transition" title="Toggle dark mode">
                            <span class="dark:hidden">🌙</span>
                            <span class="hidden dark:inline">☀️</span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
        @endauth

        @guest
        <!-- Marketing Navigation -->
    <nav x-data="{open:false, scrolled:false, services:false}" @scroll.window="scrolled = (window.pageYOffset>10)" :class="scrolled ? 'shadow-sm bg-white/90 dark:bg-gray-900/90 backdrop-blur border-b border-gray-200 dark:border-gray-700' : 'bg-transparent'" class="fixed inset-x-0 top-0 z-40 transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <span class="text-xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-600">{{ config('app.name','Embroidery') }}</span>
                    </a>
                    <div class="hidden md:flex items-center space-x-10 text-sm font-medium">
                        <a href="{{ route('home') }}" class="landing-link">Home</a>
                        <div class="relative" @mouseenter="services=true" @mouseleave="services=false">
                            <button @click="services=!services" class="landing-link inline-flex items-center">Services
                                <svg class="w-3.5 h-3.5 ml-1 transition-transform duration-300" :class="services?'rotate-180':''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                            </button>
                            <div x-cloak x-show="services" x-transition @click.outside="services=false" class="absolute left-0 mt-3 w-60 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-hidden">
                                <div class="py-2 text-sm">
                                    <a href="{{ route('services.embroidery') }}" class="flex px-4 py-2 hover:bg-indigo-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">Embroidery Digitizing</a>
                                    <a href="{{ route('services.stitch') }}" class="flex px-4 py-2 hover:bg-indigo-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">Stitch Estimator</a>
                                    <a href="{{ route('services.vector') }}" class="flex px-4 py-2 hover:bg-indigo-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">Vector Tracing</a>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('about') }}" class="landing-link">About Us</a>
                        <a href="{{ route('contact') }}" class="landing-link">Contact Us</a>
                    </div>
                    <div class="hidden md:flex items-center space-x-4">
                        @auth
                            <a href="{{ auth()->user()->isAdmin()? route('admin.dashboard'): route('dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-semibold bg-indigo-600 text-white shadow hover:bg-indigo-500 transition">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-indigo-600">Login</a>
                            <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-semibold bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow hover:from-indigo-500 hover:to-purple-500 transition">Sign Up</a>
                        @endauth
                        <button type="button" onclick="window.toggleTheme()" class="px-3 py-2 rounded-md text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-white/10 dark:hover:bg-gray-700" title="Toggle dark mode">
                            <span class="dark:hidden">🌙</span>
                            <span class="hidden dark:inline">☀️</span>
                        </button>
                    </div>
                    <button @click="open=!open" class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-md focus:outline-none hover:bg-white/10 text-gray-700 dark:text-gray-200" :class="scrolled ? 'hover:bg-gray-100 dark:hover:bg-gray-800' : ''" aria-label="Toggle navigation">
                        <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div x-cloak x-show="open" x-transition class="md:hidden border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-b-lg shadow-sm">
                    <div class="px-4 py-4 space-y-3 text-sm font-medium">
                        <a @click="open=false" href="{{ route('home') }}" class="mobile-link">Home</a>
                        <div class="border rounded-md border-gray-200 dark:border-gray-700" x-data="{s:false}">
                            <button @click="s=!s" class="w-full flex justify-between items-center px-3 py-2 text-left">Services
                                <svg class="w-4 h-4 transition-transform" :class="s?'rotate-180':''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                            </button>
                            <div x-show="s" x-collapse class="pb-2">
                                <a @click="open=false" href="{{ route('services.embroidery') }}" class="block pl-6 pr-3 py-1.5 mobile-link">Embroidery Digitizing</a>
                                <a @click="open=false" href="{{ route('services.stitch') }}" class="block pl-6 pr-3 py-1.5 mobile-link">Stitch Estimator</a>
                                <a @click="open=false" href="{{ route('services.vector') }}" class="block pl-6 pr-3 py-1.5 mobile-link">Vector Tracing</a>
                            </div>
                        </div>
                        <a @click="open=false" href="{{ route('about') }}" class="mobile-link">About Us</a>
                        <a @click="open=false" href="{{ route('contact') }}" class="mobile-link">Contact Us</a>
                        <div class="pt-2 flex items-center space-x-3">
                            @auth
                                <a href="{{ auth()->user()->isAdmin()? route('admin.dashboard'): route('dashboard') }}" class="flex-1 text-center px-4 py-2 rounded-md bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="flex-1 text-center px-4 py-2 rounded-md border text-gray-700 dark:text-gray-300 hover:border-indigo-400 hover:text-indigo-600">Login</a>
                                <a href="{{ route('register') }}" class="flex-1 text-center px-4 py-2 rounded-md bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Sign Up</a>
                            @endauth
                            <button type="button" onclick="window.toggleTheme(); open=false" class="flex-0 text-center px-3 py-2 rounded-md border text-gray-600 dark:text-gray-300 hover:border-indigo-400 hover:text-indigo-600">🌓</button>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <style>
            .landing-link{position:relative;display:inline-flex;align-items:center;color:#4b5563;padding:0.25rem 0;}
            .landing-link:after{content:"";position:absolute;left:0;bottom:-4px;height:2px;width:0;background:linear-gradient(to right,#6366f1,#8b5cf6);transition:.3s;}
            .landing-link:hover{color:#4338ca;}
            .landing-link:hover:after{width:100%;}
            .mobile-link{display:block;color:#374151;padding:.5rem .25rem;}
            .mobile-link:hover{color:#4f46e5;}
            .nav-link{position:relative;padding:.5rem .25rem;color:#4b5563;font-weight:500;}
            .nav-link.active{color:#1f2937;}
            .nav-link:after{content:"";position:absolute;left:0;bottom:0;height:2px;width:0;background:#6366f1;transition:.3s;}
            .nav-link:hover:after,.nav-link.active:after{width:100%;}
            html.dark .landing-link{color:#9ca3af;}
            html.dark .landing-link:hover{color:#c7d2fe;}
            html.dark .mobile-link{color:#d1d5db;}
            html.dark .mobile-link:hover{color:#818cf8;}
            html.dark .nav-link{color:#9ca3af;}
            html.dark .nav-link.active{color:#f3f4f6;}
        </style>
        <script>
            window.toggleTheme = function(){
                const el = document.documentElement;
                const dark = el.classList.toggle('dark');
                localStorage.setItem('theme', dark ? 'dark' : 'light');
            };
        </script>
        @endguest

        <!-- Page Content -->
        <main class="py-4">
            @if (session('success'))
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-4">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-4">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
