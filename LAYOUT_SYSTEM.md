# Layout System Documentation

## Overview
The application now uses a **3-layout system** for better organization and user experience:

## Layout Types

### 1. Landing Layout (`layouts.landing`)
**Purpose**: Marketing and public pages
**Usage**: `@extends('layouts.landing')`

**Features**:
- ✨ **Hero-style navigation** with transparent/blur effect on scroll
- 🎨 **Enhanced services dropdown** with icons and descriptions  
- 🦶 **Complete footer** with company info and links
- 📱 **Mobile-optimized** navigation with collapsible menus
- 🌓 **Dark mode** support with theme toggle

**Used For**:
- Home page (`landing.blade.php`)
- About page (`static/about.blade.php`)
- Contact page (`static/contact.blade.php`)
- Service pages (`static/services/*.blade.php`)

### 2. Dashboard Layout (`layouts.dashboard`)
**Purpose**: Authenticated user interface
**Usage**: `@extends('layouts.dashboard')`

**Features**:
- 🏠 **Sticky navigation** with backdrop blur
- 👤 **User profile dropdown** with avatar and settings
- 🔔 **Notification system** integration
- 📊 **Role-based navigation** (Admin vs Customer menus)
- ✅ **Enhanced message alerts** with dismiss buttons
- 🎨 **Professional styling** optimized for data interfaces

**Used For**:
- Dashboard (`dashboard.blade.php`)
- Quote requests (`quote-requests/*.blade.php`)
- Orders (`orders/*.blade.php`)
- Invoices (`invoices/*.blade.php`)
- Profile (`profile/*.blade.php`)
- Payments (`payments/*.blade.php`)

### 3. Auth Layout (`layouts.auth`)
**Purpose**: Authentication pages
**Usage**: `@extends('layouts.auth')`

**Features**:
- 🎨 **Clean minimal design** with gradient background
- 🔒 **Focused authentication** experience
- 📱 **Mobile-optimized** forms
- 🌓 **Theme toggle** support
- ✨ **Subtle grid pattern** background

**Used For**:
- Login (`auth/login.blade.php`)
- Register (`auth/register.blade.php`)

## Navigation Structure

### Landing Layout Navigation
```
Home | Services ▼ | About | Contact | [Login] [Get Started]
       ├─ Embroidery Digitizing
       ├─ Stitch Estimator  
       └─ Vector Tracing
```

### Dashboard Layout Navigation
```
[Logo] | Home | Services ▼ | About | Contact | [Role-Based Menu] | [Profile ▼] [🔔] [🌓]

Admin Menu:    Customer Menu:
├─ Admin       ├─ Dashboard
├─ Users       ├─ Quotes
├─ Orders      ├─ Orders
├─ Invoices    └─ Invoices
└─ Testimonials
```

## Migration Guide

### From Old `layouts.app` to New Layouts:

1. **Static/Marketing Pages** → `layouts.landing`
2. **User Dashboard Pages** → `layouts.dashboard`  
3. **Login/Register Pages** → `layouts.auth`

### Legacy Support
- The original `layouts.app` now shows a migration notice
- Pages still using `layouts.app` will display guidance to developers
- Fallback functionality maintains compatibility

## Key Features

### ✨ Enhanced User Experience
- **Contextual Navigation**: Different layouts for different user states
- **Professional Styling**: Consistent design language across all layouts
- **Mobile-First**: Responsive design optimized for all devices

### 🛠️ Developer Benefits
- **Clear Separation**: Logical layout organization
- **Reusable Components**: Shared styles and scripts
- **Easy Maintenance**: Focused layout responsibilities

### 🎨 Design Consistency
- **Unified Branding**: Consistent logo and color scheme
- **Theme Support**: Dark/light mode across all layouts
- **Typography**: Consistent font usage (Figtree)

## Technical Details

### Shared Features Across All Layouts:
- Tailwind CSS via CDN
- Alpine.js for interactivity
- Dark mode with localStorage persistence
- CSRF token support
- Responsive design
- Font loading optimization

### Performance Optimizations:
- Backdrop blur effects for modern feel
- Efficient CSS with Tailwind
- Minimal JavaScript footprint
- Font preloading

## File Structure
```
resources/views/layouts/
├── landing.blade.php     # Marketing pages
├── dashboard.blade.php   # Authenticated users  
├── auth.blade.php       # Login/register
└── app.blade.php        # Legacy/migration notice
```

## Usage Examples

### Landing Page
```php
@extends('layouts.landing')
@section('title', 'About Us')
@section('description', 'Learn about our embroidery services')
@section('content')
    <!-- Page content -->
@endsection
```

### Dashboard Page
```php
@extends('layouts.dashboard')
@section('title', 'My Orders')
@section('content')
    <!-- Dashboard content -->
@endsection
```

### Auth Page
```php
@extends('layouts.auth')
@section('title', 'Login')
@section('content')
    <!-- Auth form -->
@endsection
```

---

**Last Updated**: September 6, 2025
**Version**: 2.0.0
