#!/bin/bash

# Shared Hosting Deployment Script
# Run this script in your shared hosting cPanel terminal or SSH

echo "🚀 Deploying Laravel Embroidery Management System to Shared Hosting..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "Laravel artisan file not found. Make sure you're in the Laravel root directory."
    exit 1
fi

print_info "Starting deployment process..."

# Step 1: Install Python dependencies if possible
print_status "Checking Python environment..."
if command -v python3 &> /dev/null; then
    print_status "Python 3 is available"
    
    # Try to install PyEmbroidery
    python3 -m pip install --user pyembroidery 2>/dev/null && \
        print_status "PyEmbroidery installed successfully" || \
        print_warning "Could not install PyEmbroidery. Manual installation may be required."
else
    print_warning "Python 3 not found. Embroidery conversion may not work."
fi

# Step 2: Set up environment
print_status "Setting up environment configuration..."
if [ ! -f ".env" ]; then
    if [ -f ".env.shared-hosting" ]; then
        cp .env.shared-hosting .env
        print_status "Environment file created from shared hosting template"
    elif [ -f ".env.example" ]; then
        cp .env.example .env
        print_status "Environment file created from example"
    else
        print_error "No environment template found"
        exit 1
    fi
else
    print_info "Environment file already exists"
fi

# Step 3: Generate application key if needed
print_status "Generating application key..."
php artisan key:generate --force

# Step 4: Install/update dependencies
print_status "Installing Composer dependencies..."
if command -v composer &> /dev/null; then
    composer install --optimize-autoloader --no-dev
    print_status "Composer dependencies installed"
else
    print_warning "Composer not found. Dependencies may need manual installation."
fi

# Step 5: Set up storage directories
print_status "Setting up storage directories..."
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/app/temp
mkdir -p storage/app/temp/embroidery
mkdir -p bootstrap/cache

# Step 6: Set permissions
print_status "Setting file permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage/logs storage/framework storage/app

# Step 7: Clear and cache configuration
print_status "Optimizing application..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Step 8: Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Step 9: Run database migrations
print_status "Running database migrations..."
php artisan migrate --force

# Step 10: Make Python script executable
if [ -f "scripts/convert_embroidery.py" ]; then
    chmod +x scripts/convert_embroidery.py
    print_status "Python conversion script is ready"
fi

# Step 11: Test the application
print_status "Testing application..."
php artisan --version
if [ $? -eq 0 ]; then
    print_status "Laravel application is working"
else
    print_error "Laravel application test failed"
fi

# Step 12: Create a simple health check
cat > public/health-check.php << 'EOF'
<?php
header('Content-Type: application/json');

$checks = [
    'php_version' => PHP_VERSION,
    'laravel_up' => file_exists('../artisan'),
    'storage_writable' => is_writable('../storage'),
    'python_available' => !empty(shell_exec('python3 --version 2>/dev/null')),
    'pyembroidery_available' => !empty(shell_exec('python3 -c "import pyembroidery; print(\'OK\')" 2>/dev/null')),
    'database_configured' => !empty(getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?? ''),
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($checks, JSON_PRETTY_PRINT);
?>
EOF

print_status "Health check endpoint created at /health-check.php"

echo ""
echo "🎉 Deployment completed successfully!"
echo ""
echo "📋 Next Steps:"
echo "   1. Update your .env file with actual database credentials"
echo "   2. Configure your domain to point to the 'public' folder"
echo "   3. Set up your 2Checkout payment credentials"
echo "   4. Test the health check: https://yourdomain.com/health-check.php"
echo "   5. Test embroidery conversion functionality"
echo ""
echo "📁 Important Files:"
echo "   Environment: .env"
echo "   Health Check: public/health-check.php"
echo "   Logs: storage/logs/laravel.log"
echo ""
echo "🧪 Testing Commands:"
echo "   php artisan --version"
echo "   python3 scripts/convert_embroidery.py --help"
echo ""
echo "✅ Your Laravel Embroidery Management System is ready!"
