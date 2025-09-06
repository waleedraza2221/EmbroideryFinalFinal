#!/bin/bash

# Supabase Database Migration Script
# Run this after setting up your Laravel application with Supabase

echo "🗄️  Setting up Supabase database for Laravel Embroidery Management..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
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

# Check if .env file exists
if [ ! -f .env ]; then
    print_error ".env file not found. Please create it first with Supabase credentials."
    exit 1
fi

# Check if Supabase credentials are configured
if ! grep -q "DB_CONNECTION=pgsql" .env; then
    print_error ".env file not configured for Supabase. Please update DB_CONNECTION to pgsql."
    exit 1
fi

print_status "Testing database connection..."

# Test database connection
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'Database connection successful!'; } catch (Exception \$e) { echo 'Connection failed: ' . \$e->getMessage(); exit(1); }"

if [ $? -ne 0 ]; then
    print_error "Database connection failed. Please check your Supabase credentials."
    exit 1
fi

print_status "Database connection successful!"

# Install additional packages if needed
print_status "Installing PostgreSQL support packages..."
if [ -f composer.json ]; then
    composer require doctrine/dbal --no-interaction
fi

# Clear any cached config
print_status "Clearing Laravel cache..."
php artisan config:clear
php artisan cache:clear

# Run migrations
print_status "Running database migrations..."
php artisan migrate --force

if [ $? -eq 0 ]; then
    print_status "Migrations completed successfully!"
else
    print_error "Migration failed. Please check the error above."
    exit 1
fi

# Run seeders if they exist
if [ -f database/seeders/DatabaseSeeder.php ]; then
    print_warning "Would you like to run database seeders? (y/n)"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
        php artisan db:seed
        print_status "Database seeders completed!"
    fi
fi

# Cache optimized configurations
print_status "Optimizing Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link if it doesn't exist
if [ ! -L public/storage ]; then
    php artisan storage:link
    print_status "Storage link created!"
fi

# Set proper permissions
print_status "Setting file permissions..."
chmod -R 775 storage bootstrap/cache

print_status "Supabase database setup completed successfully!"
echo ""
echo "📋 Next Steps:"
echo "1. Test your application at your domain"
echo "2. Try the embroidery format converter"
echo "3. Monitor your Supabase dashboard for usage"
echo ""
echo "🔗 Useful Links:"
echo "- Supabase Dashboard: https://supabase.com/dashboard"
echo "- Your Project URL: $(grep SUPABASE_URL .env | cut -d'=' -f2)"
echo ""
echo "✅ Your Laravel application is ready with Supabase!"
