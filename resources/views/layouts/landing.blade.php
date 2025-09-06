<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Embroidery') }} - @yield('title', 'Professional Embroidery Services')</title>
    <meta name="description" content="@yield('description', 'Professional embroidery digitizing, vector tracing, and custom design services. Get your quote today!')">

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
<body class="font-sans antialiased bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">
    <!-- Landing Navigation -->
    <nav x-data="{open:false, scrolled:false, services:false}" 
         @scroll.window="scrolled = (window.pageYOffset>10)" 
         :class="scrolled ? 'shadow-lg bg-white/95 dark:bg-gray-900/95 backdrop-blur border-b border-gray-200 dark:border-gray-700' : 'bg-white/10 backdrop-blur-sm'" 
         class="fixed inset-x-0 top-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">E</span>
                    </div>
                    <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-700 to-purple-600">
                        {{ config('app.name','Embroidery Digitize') }}
                    </span>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8 text-sm font-medium">
                    <a href="{{ route('home') }}" :class="scrolled ? 'nav-link-scrolled' : 'landing-link'" class="nav-base {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                    
                    <!-- Services Dropdown -->
                    <div class="relative" @mouseenter="services=true" @mouseleave="services=false">
                        <button @click="services=!services" :class="scrolled ? 'nav-link-scrolled' : 'landing-link'" class="nav-base inline-flex items-center {{ request()->routeIs('services.*') ? 'active' : '' }}">
                            Services
                            <svg class="w-3.5 h-3.5 ml-1 transition-transform duration-300" :class="services?'rotate-180':''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                            </svg>
                        </button>
                        <div x-cloak x-show="services" x-transition.origin.top.left @click.outside="services=false" 
                             class="absolute left-0 mt-3 w-64 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl overflow-hidden">
                            <div class="py-2">
                                <a href="{{ route('services.embroidery') }}" class="flex items-center px-4 py-3 hover:bg-indigo-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">
                                    <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mr-3">
                                        <span class="text-indigo-600 dark:text-indigo-400 text-sm">🎨</span>
                                    </div>
                                    <div>
                                        <div class="font-medium">Embroidery Digitizing</div>
                                        <div class="text-xs text-gray-500">Convert artwork to embroidery</div>
                                    </div>
                                </a>
                                <a href="{{ route('services.stitch') }}" class="flex items-center px-4 py-3 hover:bg-indigo-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">
                                    <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mr-3">
                                        <span class="text-indigo-600 dark:text-indigo-400 text-sm">📏</span>
                                    </div>
                                    <div>
                                        <div class="font-medium">Stitch Estimator</div>
                                        <div class="text-xs text-gray-500">Calculate stitch counts</div>
                                    </div>
                                </a>
                                <a href="{{ route('services.vector') }}" class="flex items-center px-4 py-3 hover:bg-indigo-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">
                                    <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mr-3">
                                        <span class="text-indigo-600 dark:text-indigo-400 text-sm">🔍</span>
                                    </div>
                                    <div>
                                        <div class="font-medium">Vector Tracing</div>
                                        <div class="text-xs text-gray-500">High-quality vector conversion</div>
                                    </div>
                                </a>
                                <a href="{{ route('services.converter') }}" class="flex items-center px-4 py-3 hover:bg-indigo-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">
                                    <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mr-3">
                                        <span class="text-indigo-600 dark:text-indigo-400 text-sm">🔄</span>
                                    </div>
                                    <div>
                                        <div class="font-medium">Format Converter</div>
                                        <div class="text-xs text-gray-500">Convert between formats</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('about') }}" :class="scrolled ? 'nav-link-scrolled' : 'landing-link'" class="nav-base {{ request()->routeIs('about') ? 'active' : '' }}">About</a>
                    <a href="{{ route('contact') }}" :class="scrolled ? 'nav-link-scrolled' : 'landing-link'" class="nav-base {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                </div>

                <!-- Desktop Auth Buttons -->
                <div class="hidden md:flex items-center space-x-4">
                    @auth
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" 
                           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-white text-indigo-600 shadow-md hover:bg-gray-50 transition-all hover:shadow-lg">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" :class="scrolled ? 'text-gray-600 hover:text-indigo-600' : 'text-white hover:text-yellow-200'" class="text-sm font-medium transition-colors px-3 py-2 rounded-md hover:bg-white/10">
                            Login
                        </a>
                        <a href="{{ route('register') }}" 
                           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-white text-indigo-600 shadow-md hover:bg-gray-50 transition-all hover:shadow-lg">
                            Get Started
                        </a>
                    @endauth
                    
                    <!-- Theme Toggle -->
                    <button type="button" onclick="window.toggleTheme()" 
                            :class="scrolled ? 'text-gray-600 hover:bg-gray-100' : 'text-white hover:bg-white/10'" 
                            class="p-2 rounded-lg transition-colors" 
                            title="Toggle dark mode">
                        <span class="dark:hidden">🌙</span>
                        <span class="hidden dark:inline">☀️</span>
                    </button>
                </div>

                <!-- Mobile Menu Button -->
                <button @click="open=!open" class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-gray-200" aria-label="Toggle navigation">
                    <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Mobile Navigation -->
            <div x-cloak x-show="open" x-transition class="md:hidden border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-b-xl shadow-lg mt-1">
                <div class="px-4 py-4 space-y-3">
                    <a @click="open=false" href="{{ route('home') }}" class="mobile-link">Home</a>
                    
                    <!-- Mobile Services -->
                    <div class="border rounded-lg border-gray-200 dark:border-gray-700" x-data="{s:false}">
                        <button @click="s=!s" class="w-full flex justify-between items-center px-3 py-2 text-left text-gray-700 dark:text-gray-300">
                            Services
                            <svg class="w-4 h-4 transition-transform" :class="s?'rotate-180':''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                            </svg>
                        </button>
                        <div x-show="s" x-collapse class="pb-2 space-y-1">
                            <a @click="open=false" href="{{ route('services.embroidery') }}" class="block pl-6 pr-3 py-2 mobile-link text-sm">Embroidery Digitizing</a>
                            <a @click="open=false" href="{{ route('services.stitch') }}" class="block pl-6 pr-3 py-2 mobile-link text-sm">Stitch Estimator</a>
                            <a @click="open=false" href="{{ route('services.vector') }}" class="block pl-6 pr-3 py-2 mobile-link text-sm">Vector Tracing</a>
                            <a @click="open=false" href="{{ route('services.converter') }}" class="block pl-6 pr-3 py-2 mobile-link text-sm">Format Converter</a>
                        </div>
                    </div>
                    
                    <a @click="open=false" href="{{ route('about') }}" class="mobile-link">About</a>
                    <a @click="open=false" href="{{ route('contact') }}" class="mobile-link">Contact</a>
                    
                    <!-- Mobile Auth -->
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-3">
                        @auth
                            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" 
                               class="block w-full text-center px-4 py-3 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-500">
                                Dashboard
                            </a>
                        @else
                            <div class="grid grid-cols-2 gap-3">
                                <a href="{{ route('login') }}" class="text-center px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-indigo-400 hover:text-indigo-600">
                                    Login
                                </a>
                                <a href="{{ route('register') }}" class="text-center px-4 py-3 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-500">
                                    Sign Up
                                </a>
                            </div>
                        @endauth
                        
                        <button type="button" onclick="window.toggleTheme(); open=false" 
                                class="w-full text-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-indigo-400">
                            Toggle Theme 🌓
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">E</span>
                        </div>
                        <span class="text-xl font-bold">{{ config('app.name', 'Embroidery') }}</span>
                    </div>
                    <p class="text-gray-400 mb-4 max-w-md">
                        Professional embroidery digitizing services with fast turnaround times and exceptional quality. 
                        Transform your ideas into beautiful embroidered designs.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <span class="sr-only">Facebook</span>
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <span class="sr-only">Instagram</span>
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.987 11.987s11.987-5.367 11.987-11.987C23.973 5.367 18.634.001 12.017.001zM8.25 18.75h-2.445V8.467H8.25v10.283zm-1.22-11.69c-.784 0-1.42-.636-1.42-1.42s.636-1.42 1.42-1.42 1.42.636 1.42 1.42-.636 1.42-1.42 1.42zm13.94 11.69h-2.445v-5.569c0-.972-.02-2.222-1.354-2.222-1.355 0-1.563 1.058-1.563 2.15v5.641H13.16V8.467h2.347v1.408h.033c.327-.62 1.126-1.27 2.32-1.27 2.48 0 2.937 1.632 2.937 3.75v6.395z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Services</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('services.embroidery') }}" class="hover:text-white transition-colors">Embroidery Digitizing</a></li>
                        <li><a href="{{ route('services.stitch') }}" class="hover:text-white transition-colors">Stitch Estimator</a></li>
                        <li><a href="{{ route('services.vector') }}" class="hover:text-white transition-colors">Vector Tracing</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Support</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">About Us</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition-colors">Contact</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">FAQ</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'Embroidery') }}. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Styles -->
    <style>
        .nav-base {
            position: relative;
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .landing-link {
            color: #ffffff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .nav-link-scrolled {
            color: #374151;
        }
        
        .nav-base:after {
            content: "";
            position: absolute;
            left: 0.75rem;
            right: 0.75rem;
            bottom: 0;
            height: 2px;
            width: 0;
            transition: width 0.3s ease;
        }
        
        .landing-link:after {
            background: linear-gradient(to right, #ffffff, #fbbf24);
        }
        
        .nav-link-scrolled:after {
            background: linear-gradient(to right, #6366f1, #8b5cf6);
        }
        
        .nav-base:hover {
            transform: translateY(-1px);
        }
        
        .landing-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .nav-link-scrolled:hover {
            color: #1f2937;
            background-color: #f3f4f6;
        }
        
        .nav-base.active {
            background-color: rgba(255, 255, 255, 0.15);
        }
        
        .landing-link.active {
            color: #ffffff;
        }
        
        .nav-link-scrolled.active {
            color: #1f2937;
            background-color: #e0e7ff;
        }
        
        .nav-base:hover:after,
        .nav-base.active:after {
            width: calc(100% - 1.5rem);
        }
        
        .mobile-link {
            display: block;
            color: #374151;
            padding: 0.75rem 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .mobile-link:hover {
            color: #4f46e5;
            background-color: #f3f4f6;
        }
        html.dark .landing-link {
            color: #ffffff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        html.dark .nav-link-scrolled {
            color: #d1d5db;
        }
        html.dark .nav-link-scrolled:hover {
            color: #f3f4f6;
            background-color: #374151;
        }
        html.dark .nav-link-scrolled.active {
            color: #f3f4f6;
            background-color: #4338ca;
        }
        html.dark .mobile-link {
            color: #d1d5db;
        }
        html.dark .mobile-link:hover {
            color: #818cf8;
            background-color: #374151;
        }
    </style>

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
